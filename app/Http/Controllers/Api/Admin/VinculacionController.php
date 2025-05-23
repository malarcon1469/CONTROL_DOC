<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vinculacion;
use App\Models\EmpresaMandante; // Necesario para validación
use Illuminate\Http\Request;
use App\Http\Resources\VinculacionResource; // Lo crearemos en el siguiente paso
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Para transacciones si se vuelve más complejo

class VinculacionController extends Controller
{
    /**
     * Display a listing of the resource.
     * Permite filtrar por empresa_mandante_id.
     * Opcionalmente, carga jerarquía (children).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vinculacion::query();

        if ($request->has('empresa_mandante_id') && $request->input('empresa_mandante_id') !== null) {
            $request->validate([
                'empresa_mandante_id' => ['required', 'integer', Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at')]
            ]);
            $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'));
        }

        // Por defecto, listar solo las vinculaciones de nivel superior (sin parent_id)
        // si no se especifica un mandante o si se quiere una vista jerárquica inicial.
        // Si se filtra por mandante, usualmente se quieren todas las de ese mandante.
        // Si se quiere una vista jerárquica completa, se puede añadir un parámetro como ?jerarquia=true
        // y cargar 'children' recursivamente, o el frontend maneja esto.
        // Por simplicidad, cargamos 'empresaMandante' y 'parent'.
        if ($request->boolean('jerarquia_completa') && $request->has('empresa_mandante_id')) {
            // Cargar todas las vinculaciones del mandante y que el frontend arme el árbol,
            // o usar una librería/lógica para estructuras jerárquicas.
            // Para una API simple, devolverlas planas puede ser suficiente y el cliente las organiza.
            // Aquí cargamos 'children' para un nivel, se puede hacer recursivo si es necesario.
            $vinculaciones = $query->with(['empresaMandante', 'parent', 'children'])
                                  // ->whereNull('parent_id') // Para obtener solo raíces si se quiere jerarquía desde el inicio
                                  ->get();
        } else {
            $vinculaciones = $query->with(['empresaMandante', 'parent'])->get();
        }


        return VinculacionResource::collection($vinculaciones)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_vinculacion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vinculaciones', 'nombre_vinculacion')->where(function ($query) use ($request) {
                    return $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'))
                                 ->where('parent_id', $request->input('parent_id')); // Único nombre bajo el mismo padre y mandante
                })->withoutTrashed(),
            ],
            'descripcion_vinculacion' => 'nullable|string|max:1000',
            'empresa_mandante_id' => [
                'required',
                'integer',
                Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at'),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                // Si parent_id se proporciona, debe existir en la tabla vinculaciones
                Rule::exists('vinculaciones', 'id')->where(function ($query) use ($request) {
                    // Y el parent_id debe pertenecer a la MISMA empresa_mandante_id
                    return $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'))
                                 ->whereNull('deleted_at');
                }),
                // Evitar auto-referencia directa
                function ($attribute, $value, $fail) use ($request) {
                    // Esta validación no es necesaria aquí ya que no tenemos el ID del registro actual aún.
                    // Se aplica en el update.
                },
            ],
        ]);

        $vinculacion = Vinculacion::create($validatedData);
        $vinculacion->load(['empresaMandante', 'parent', 'children']);

        return (new VinculacionResource($vinculacion))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $vinculacion = Vinculacion::with(['empresaMandante', 'parent', 'children.children']) // Cargar hasta 2 niveles de hijos
                               ->find($id);

        if (!$vinculacion) {
            return response()->json(['message' => 'Vinculación no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return (new VinculacionResource($vinculacion))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $vinculacion = Vinculacion::find($id);

        if (!$vinculacion) {
            return response()->json(['message' => 'Vinculación no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'nombre_vinculacion' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('vinculaciones', 'nombre_vinculacion')->ignore($vinculacion->id)->where(function ($query) use ($request, $vinculacion) {
                    // Mandante para la validación unique: el que viene en el request o el existente.
                    $empresaMandanteIdParaValidar = $request->input('empresa_mandante_id', $vinculacion->empresa_mandante_id);
                    // Parent para la validación unique: el que viene en el request o el existente.
                    $parentIdParaValidar = $request->input('parent_id', $vinculacion->parent_id);
                    
                    return $query->where('empresa_mandante_id', $empresaMandanteIdParaValidar)
                                 ->where('parent_id', $parentIdParaValidar);
                })->withoutTrashed(),
            ],
            'descripcion_vinculacion' => 'nullable|string|max:1000',
            'empresa_mandante_id' => [ // El mandante de una vinculación NO debería cambiar una vez creada.
                                       // Si se permite cambiar, se complica la jerarquía.
                                       // Por ahora, validamos que si se envía, sea el mismo. O no permitirlo.
                'sometimes',
                'required',
                'integer',
                Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at'),
                // Asegurar que si se intenta cambiar el mandante, no se permita o se maneje la lógica de mover sub-árbol.
                // Por simplicidad, se podría prohibir el cambio de empresa_mandante_id para una vinculación existente.
                function ($attribute, $value, $fail) use ($vinculacion) {
                    if ($vinculacion->empresa_mandante_id !== (int)$value) {
                        $fail('No se puede cambiar la empresa mandante de una vinculación existente.');
                    }
                },
            ],
            'parent_id' => [
                'sometimes', // Se puede enviar null para convertirla en raíz
                'nullable',
                'integer',
                Rule::exists('vinculaciones', 'id')->where(function ($query) use ($request, $vinculacion) {
                    // El parent debe pertenecer al mismo mandante (el mandante actual del ítem o el que se está intentando setear)
                     $empresaMandanteIdDelContexto = $request->input('empresa_mandante_id', $vinculacion->empresa_mandante_id);
                    return $query->where('empresa_mandante_id', $empresaMandanteIdDelContexto)
                                 ->whereNull('deleted_at');
                }),
                // Evitar que el parent_id sea el mismo id del registro que se está actualizando (auto-referencia directa)
                Rule::notIn([$vinculacion->id]),
                // Evitar que el parent_id sea uno de sus propios descendientes (referencia circular)
                function ($attribute, $value, $fail) use ($vinculacion) {
                    if ($value !== null) {
                        $ancestro = Vinculacion::find($value);
                        while ($ancestro) {
                            if ($ancestro->id === $vinculacion->id) {
                                $fail('No se puede asignar la vinculación como hija de uno de sus propios descendientes (referencia circular).');
                                return;
                            }
                            $ancestro = $ancestro->parent;
                        }
                    }
                },
            ],
        ]);
        
        // Si empresa_mandante_id no se envió, no estará en $validatedData y no se actualizará.
        // La validación personalizada ya impide que cambie si se envía un valor diferente.
        // Por lo tanto, si pasa la validación, 'empresa_mandante_id' o no se toca, o es el mismo.
        
        $vinculacion->update($validatedData);
        $vinculacion->load(['empresaMandante', 'parent', 'children']);

        return (new VinculacionResource($vinculacion))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $vinculacion = Vinculacion::find($id);

        if (!$vinculacion) {
            return response()->json(['message' => 'Vinculación no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        // VERIFICACIÓN IMPORTANTE: No eliminar si tiene hijos.
        if ($vinculacion->children()->exists()) {
            return response()->json(['message' => 'No se puede eliminar la vinculación porque tiene vinculaciones hijas asociadas. Elimine o reasigne las hijas primero.'], Response::HTTP_CONFLICT);
        }
        
        // Considerar verificación si la vinculación está en uso (ej. en configuraciones_documentos_mandante)
        // if ($vinculacion->configuracionesDocumentosMandante()->exists()) {
        //     return response()->json(['message' => 'No se puede eliminar la vinculación, está en uso en configuraciones de documentos.'], Response::HTTP_CONFLICT);
        // }

        $vinculacion->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}