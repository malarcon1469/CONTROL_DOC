<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpresaMandante; // Asegúrate que el namespace del modelo sea correcto
use Illuminate\Http\Request;
use App\Http\Resources\EmpresaMandanteResource; // Asegúrate que el namespace del resource sea correcto
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class EmpresaMandanteController extends Controller
{
    /**
     * Muestra un listado del recurso.
     */
    public function index(): JsonResponse
    {
        // Podríamos añadir eager loading para relaciones comunes si siempre se muestran
        // $empresas = EmpresaMandante::withCount(['cargos', 'vinculaciones'])->get();
        $empresas = EmpresaMandante::all();
        return EmpresaMandanteResource::collection($empresas)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'rut_empresa_mandante' => [
                'required',
                'string',
                'max:20', // Ajusta según el formato de RUT que uses
                Rule::unique('empresas_mandantes', 'rut_empresa_mandante')->withoutTrashed(),
            ],
            'nombre_empresa_mandante' => 'required|string|max:255',
            'razon_social_mandante' => 'nullable|string|max:255',
            'direccion_mandante' => 'nullable|string|max:255',
            'ciudad_mandante' => 'nullable|string|max:100',
            'telefono_mandante' => 'nullable|string|max:50',
            'email_mandante' => 'nullable|email|max:255',
            'nombre_contacto_mandante' => 'nullable|string|max:255',
            'email_contacto_mandante' => 'nullable|email|max:255',
            'telefono_contacto_mandante' => 'nullable|string|max:50',
            'activa' => 'required|boolean',
        ]);

        $empresa = EmpresaMandante::create($validatedData);

        return (new EmpresaMandanteResource($empresa))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show($id): JsonResponse
    {
        // $empresa = EmpresaMandante::with(['cargos', 'vinculaciones'])->find($id); // Ejemplo con eager loading
        $empresa = EmpresaMandante::find($id);

        if (!$empresa) {
            return response()->json(['message' => 'Empresa mandante no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return (new EmpresaMandanteResource($empresa))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $empresa = EmpresaMandante::find($id);

        if (!$empresa) {
            return response()->json(['message' => 'Empresa mandante no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'rut_empresa_mandante' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('empresas_mandantes', 'rut_empresa_mandante')
                    ->ignore($empresa->id)
                    ->withoutTrashed(),
            ],
            'nombre_empresa_mandante' => 'sometimes|required|string|max:255',
            'razon_social_mandante' => 'nullable|string|max:255',
            'direccion_mandante' => 'nullable|string|max:255',
            'ciudad_mandante' => 'nullable|string|max:100',
            'telefono_mandante' => 'nullable|string|max:50',
            'email_mandante' => 'nullable|email|max:255',
            'nombre_contacto_mandante' => 'nullable|string|max:255',
            'email_contacto_mandante' => 'nullable|email|max:255',
            'telefono_contacto_mandante' => 'nullable|string|max:50',
            'activa' => 'sometimes|required|boolean',
        ]);

        $empresa->update($validatedData);

        return (new EmpresaMandanteResource($empresa))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy($id): JsonResponse
    {
        $empresa = EmpresaMandante::find($id);

        if (!$empresa) {
            return response()->json(['message' => 'Empresa mandante no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        // Opcional: Verificar si la empresa mandante tiene datos asociados (cargos, vinculaciones, configuraciones de documentos)
        // antes de permitir el borrado, o manejar la eliminación en cascada/set null en las migraciones.
        // if ($empresa->cargos()->exists() || $empresa->vinculaciones()->exists() || $empresa->configuracionesDocumentosMandante()->exists()) {
        //     return response()->json(['message' => 'Esta empresa mandante tiene datos asociados y no puede ser eliminada directamente.'], Response::HTTP_CONFLICT);
        // }
        
        $empresa->delete(); // Soft delete

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}