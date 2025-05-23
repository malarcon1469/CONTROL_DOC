<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumento; // Asegúrate que el namespace del modelo sea correcto
use Illuminate\Http\Request;
use App\Http\Resources\TipoDocumentoResource; // Asegúrate que el namespace del resource sea correcto
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TipoDocumentoController extends Controller
{
    /**
     * Muestra un listado del recurso.
     */
    public function index(): JsonResponse
    {
        $tiposDocumentos = TipoDocumento::all();
        return TipoDocumentoResource::collection($tiposDocumentos)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_documentos', 'nombre')->withoutTrashed(),
            ],
            'descripcion' => 'nullable|string|max:1000',
            'es_vencible' => 'required|boolean',
            'requiere_archivo' => 'required|boolean',
        ]);

        $tipoDocumento = TipoDocumento::create($validatedData);

        return (new TipoDocumentoResource($tipoDocumento))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show($id): JsonResponse // Usamos $id para búsqueda manual
    {
        $tipoDocumento = TipoDocumento::find($id);

        if (!$tipoDocumento) {
            return response()->json(['message' => 'Tipo de documento no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return (new TipoDocumentoResource($tipoDocumento))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(Request $request, $id): JsonResponse // Usamos $id para búsqueda manual
    {
        $tipoDocumento = TipoDocumento::find($id);

        if (!$tipoDocumento) {
            return response()->json(['message' => 'Tipo de documento no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_documentos', 'nombre')
                    ->ignore($tipoDocumento->id)
                    ->withoutTrashed(),
            ],
            'descripcion' => 'nullable|string|max:1000',
            'es_vencible' => 'sometimes|required|boolean',
            'requiere_archivo' => 'sometimes|required|boolean',
        ]);

        $tipoDocumento->update($validatedData);

        return (new TipoDocumentoResource($tipoDocumento))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy($id): JsonResponse // Usamos $id para búsqueda manual
    {
        $tipoDocumento = TipoDocumento::find($id);

        if (!$tipoDocumento) {
            return response()->json(['message' => 'Tipo de documento no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Opcional: Verificar si el tipo de documento está en uso antes de permitir el borrado.
        // if ($tipoDocumento->configuracionesDocumentosMandante()->exists() || $tipoDocumento->documentosAdjuntos()->exists()) {
        //     return response()->json(['message' => 'Este tipo de documento está en uso y no puede ser eliminado.'], Response::HTTP_CONFLICT);
        // }

        $tipoDocumento->delete(); // Soft delete

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}