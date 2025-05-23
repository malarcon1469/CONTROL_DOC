<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CondicionContratistaMaestro; // Asegúrate de que el namespace del modelo sea correcto
use Illuminate\Http\Request;
use App\Http\Resources\CondicionContratistaMaestroResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CondicionContratistaMaestroController extends Controller
{
    /**
     * Muestra un listado del recurso.
     */
    public function index(): JsonResponse
    {
        $condiciones = CondicionContratistaMaestro::all();
        return CondicionContratistaMaestroResource::collection($condiciones)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_condicion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('condiciones_contratista_maestro', 'nombre_condicion')->withoutTrashed(),
            ],
            'descripcion_condicion' => 'nullable|string|max:1000',
        ]);

        $condicion = CondicionContratistaMaestro::create($validatedData);

        return (new CondicionContratistaMaestroResource($condicion))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Muestra el recurso especificado.
     * --- MÉTODO SHOW MODIFICADO PARA BÚSQUEDA MANUAL ---
     */
    public function show($id): JsonResponse // Cambiado: ya no hay type-hint para el binding automático
    {
        // Buscamos el modelo manualmente usando el ID de la ruta
        $condicionContratistaMaestro = CondicionContratistaMaestro::find($id);

        // Verificamos si el modelo fue encontrado
        if (!$condicionContratistaMaestro) {
            // Si no se encuentra, devolvemos un 404 Not Found
            return response()->json(['message' => 'Condición de contratista maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        // Si se encuentra, lo pasamos al Resource
        return (new CondicionContratistaMaestroResource($condicionContratistaMaestro))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Actualiza el recurso especificado en el almacenamiento.
     * --- MÉTODO UPDATE MODIFICADO PARA BÚSQUEDA MANUAL ---
     * (También modificaremos update y destroy para ser consistentes si show funciona así)
     */
    public function update(Request $request, $id): JsonResponse // Cambiado
    {
        $condicionContratistaMaestro = CondicionContratistaMaestro::find($id);

        if (!$condicionContratistaMaestro) {
            return response()->json(['message' => 'Condición de contratista maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'nombre_condicion' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('condiciones_contratista_maestro', 'nombre_condicion')
                    ->ignore($condicionContratistaMaestro->id) // Ahora $condicionContratistaMaestro->id es seguro
                    ->withoutTrashed(),
            ],
            'descripcion_condicion' => 'nullable|string|max:1000',
        ]);

        $condicionContratistaMaestro->update($validatedData);

        return (new CondicionContratistaMaestroResource($condicionContratistaMaestro))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Elimina el recurso especificado del almacenamiento.
     * --- MÉTODO DESTROY MODIFICADO PARA BÚSQUEDA MANUAL ---
     */
    public function destroy($id): JsonResponse // Cambiado
    {
        $condicionContratistaMaestro = CondicionContratistaMaestro::find($id);

        if (!$condicionContratistaMaestro) {
            return response()->json(['message' => 'Condición de contratista maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $condicionContratistaMaestro->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}