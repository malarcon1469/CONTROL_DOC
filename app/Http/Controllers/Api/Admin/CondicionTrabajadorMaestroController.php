<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CondicionTrabajadorMaestro;
use Illuminate\Http\Request;
use App\Http\Resources\CondicionTrabajadorMaestroResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CondicionTrabajadorMaestroController extends Controller
{
    public function index(): JsonResponse
    {
        $condiciones = CondicionTrabajadorMaestro::all();
        return CondicionTrabajadorMaestroResource::collection($condiciones)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_condicion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('condiciones_trabajador_maestro', 'nombre_condicion')->withoutTrashed(),
            ],
            'descripcion_condicion' => 'nullable|string|max:1000',
        ]);

        $condicion = CondicionTrabajadorMaestro::create($validatedData);

        return (new CondicionTrabajadorMaestroResource($condicion))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show($id): JsonResponse
    {
        $condicion = CondicionTrabajadorMaestro::find($id);

        if (!$condicion) {
            return response()->json(['message' => 'Condición de trabajador maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return (new CondicionTrabajadorMaestroResource($condicion))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $condicion = CondicionTrabajadorMaestro::find($id);

        if (!$condicion) {
            return response()->json(['message' => 'Condición de trabajador maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'nombre_condicion' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('condiciones_trabajador_maestro', 'nombre_condicion')
                    ->ignore($condicion->id)
                    ->withoutTrashed(),
            ],
            'descripcion_condicion' => 'nullable|string|max:1000',
        ]);

        $condicion->update($validatedData);

        return (new CondicionTrabajadorMaestroResource($condicion))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy($id): JsonResponse
    {
        $condicion = CondicionTrabajadorMaestro::find($id);

        if (!$condicion) {
            return response()->json(['message' => 'Condición de trabajador maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }
        
        $condicion->delete(); 

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}