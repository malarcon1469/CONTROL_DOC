<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\EmpresaMandante; // Asegúrate que este use statement está presente
use Illuminate\Http\Request;
use App\Http\Resources\CargoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CargoController extends Controller
{
    /**
     * Display a listing of the resource.
     * Opcionalmente, permite filtrar por empresa_mandante_id.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cargo::query();

        if ($request->has('empresa_mandante_id') && $request->input('empresa_mandante_id') !== null) {
            $request->validate([
                'empresa_mandante_id' => ['required', 'integer', Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at')]
            ]);
            $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'));
        }

        $cargos = $query->with('empresaMandante')->get();

        return CargoResource::collection($cargos)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     * empresa_mandante_id es ahora obligatorio.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_cargo' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cargos', 'nombre_cargo')->where(function ($query) use ($request) {
                    return $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'));
                })->withoutTrashed(),
            ],
            'descripcion_cargo' => 'nullable|string|max:1000',
            'empresa_mandante_id' => [
                'required', // Clave: Ahora es siempre requerido
                'integer',
                Rule::exists('empresas_mandantes', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
        ]);

        $cargo = Cargo::create($validatedData);
        $cargo->load('empresaMandante'); // Cargar la relación

        return (new CargoResource($cargo))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $cargo = Cargo::with('empresaMandante')->find($id);

        if (!$cargo) {
            return response()->json(['message' => 'Cargo no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Verificar que el cargo recuperado tiene una empresa_mandante_id (debido a la nueva regla)
        // Aunque la BD debería garantizarlo si las migraciones/modelos están bien.
        // Si $cargo->empresa_mandante_id es null aquí, algo anda mal en los datos o la lógica previa.

        return (new CargoResource($cargo))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     * empresa_mandante_id es ahora obligatorio si se envía para modificar.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $cargo = Cargo::find($id);

        if (!$cargo) {
            return response()->json(['message' => 'Cargo no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'nombre_cargo' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('cargos', 'nombre_cargo')->ignore($cargo->id)->where(function ($query) use ($request, $cargo) {
                    // El empresa_mandante_id para la validación unique
                    // será el que viene en el request si se está intentando cambiar,
                    // o el existente del cargo si no se intenta cambiar.
                    $empresaMandanteIdParaValidar = $request->has('empresa_mandante_id')
                        ? $request->input('empresa_mandante_id')
                        : $cargo->empresa_mandante_id;
                    
                    // Asegurarse de que el ID del mandante para la validación no sea null
                    if (is_null($empresaMandanteIdParaValidar)) {
                        // Esto no debería ocurrir si empresa_mandante_id es siempre requerido.
                        // Pero como medida de seguridad, si se da el caso, podemos forzar un error o
                        // ajustar la query. Sin embargo, la validación de 'empresa_mandante_id' abajo
                        // debería prevenir que se guarde un null si se intenta.
                        // Aquí asumimos que empresaMandanteIdParaValidar siempre tendrá un valor válido.
                        return $query->where('empresa_mandante_id', $empresaMandanteIdParaValidar);
                    }
                    return $query->where('empresa_mandante_id', $empresaMandanteIdParaValidar);

                })->withoutTrashed(),
            ],
            'descripcion_cargo' => 'nullable|string|max:1000',
            'empresa_mandante_id' => [
                'sometimes', // Solo se valida si se envía en el request
                'required',  // Si se envía, no puede ser null
                'integer',
                Rule::exists('empresas_mandantes', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
        ]);

        // Si 'empresa_mandante_id' no está en $validatedData (porque no se envió en el request 'sometimes'),
        // $cargo->update($validatedData) no intentará cambiarlo.
        // Si SÍ está, se actualizará. La validación 'required' asegura que no se ponga a null.
        $cargo->update($validatedData);
        $cargo->load('empresaMandante'); // Recargar la relación

        return (new CargoResource($cargo))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $cargo = Cargo::find($id);

        if (!$cargo) {
            return response()->json(['message' => 'Cargo no encontrado.'], Response::HTTP_NOT_FOUND);
        }
        
        // Considerar verificación si el cargo está en uso (ej. en configuraciones_documentos_mandante)
        // if ($cargo->configuracionesDocumentosMandante()->exists()) {
        //     return response()->json(['message' => 'No se puede eliminar el cargo, está en uso.'], Response::HTTP_CONFLICT);
        // }

        $cargo->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}