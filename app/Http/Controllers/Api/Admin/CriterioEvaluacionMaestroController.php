<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CriterioEvaluacionMaestro; // Asegúrate que el namespace del modelo sea correcto
use Illuminate\Http\Request;
use App\Http\Resources\CriterioEvaluacionMaestroResource; // Asegúrate que el namespace del resource sea correcto
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CriterioEvaluacionMaestroController extends Controller
{
    /**
     * Muestra un listado del recurso.
     */
    public function index(): JsonResponse
    {
        $criterios = CriterioEvaluacionMaestro::all();
        return CriterioEvaluacionMaestroResource::collection($criterios)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_criterio' => [
                'required',
                'string',
                'max:255',
                Rule::unique('criterios_evaluacion_maestro', 'nombre_criterio')->withoutTrashed(),
            ],
            'descripcion_criterio' => 'nullable|string|max:1000',
        ]);

        $criterio = CriterioEvaluacionMaestro::create($validatedData);

        return (new CriterioEvaluacionMaestroResource($criterio))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show($id): JsonResponse
    {
        $criterio = CriterioEvaluacionMaestro::find($id);

        if (!$criterio) {
            return response()->json(['message' => 'Criterio de evaluación maestro no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return (new CriterioEvaluacionMaestroResource($criterio))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $criterio = CriterioEvaluacionMaestro::find($id);

        if (!$criterio) {
            return response()->json(['message' => 'Criterio de evaluación maestro no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'nombre_criterio' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('criterios_evaluacion_maestro', 'nombre_criterio')
                    ->ignore($criterio->id)
                    ->withoutTrashed(),
            ],
            'descripcion_criterio' => 'nullable|string|max:1000',
        ]);

        $criterio->update($validatedData);

        return (new CriterioEvaluacionMaestroResource($criterio))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy($id): JsonResponse
    {
        $criterio = CriterioEvaluacionMaestro::find($id);

        if (!$criterio) {
            return response()->json(['message' => 'Criterio de evaluación maestro no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Opcional: Verificar si el criterio está en uso antes de permitir el borrado.
        // if ($criterio->configuracionesDocumentosMandante()->exists()) { // Asumiendo que existe la relación inversa correcta
        //     return response()->json(['message' => 'Este criterio de evaluación está en uso y no puede ser eliminado.'], Response::HTTP_CONFLICT);
        // }
        
        $criterio->delete(); // Soft delete

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}