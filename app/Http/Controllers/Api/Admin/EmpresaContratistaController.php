<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpresaContratista;
use App\Models\User;
use App\Models\CondicionContratistaMaestro;
use Illuminate\Http\Request;
use App\Http\Resources\EmpresaContratistaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; 
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class EmpresaContratistaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $empresas = EmpresaContratista::with(['user', 'condicionesContratistaMaestro'])->get();
        return EmpresaContratistaResource::collection($empresas)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'rut_empresa_contratista' => [
                'required', 'string', 'max:20',
                Rule::unique('empresas_contratistas', 'rut_empresa_contratista')->withoutTrashed(),
            ],
            'nombre_empresa_contratista' => 'required|string|max:255',
            'razon_social_contratista' => 'nullable|string|max:255',
            'direccion_contratista' => 'nullable|string|max:255',
            'ciudad_contratista' => 'nullable|string|max:100',
            'telefono_contratista' => 'nullable|string|max:50',
            'email_contratista' => 'nullable|email|max:255',
            'nombre_representante_legal' => 'nullable|string|max:255',
            'rut_representante_legal' => 'nullable|string|max:20',
            'email_representante_legal' => 'nullable|email|max:255',
            'telefono_representante_legal' => 'nullable|string|max:50',
            'activa' => 'required|boolean',
            'user_name' => 'required|string|max:255',
            'user_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'user_password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'condiciones_ids' => 'nullable|array',
            'condiciones_ids.*' => ['integer', Rule::exists('condiciones_contratista_maestro', 'id')->whereNull('deleted_at')],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validatedData['user_name'],
                'email' => $validatedData['user_email'],
                'password' => Hash::make($validatedData['user_password']),
            ]);
            $user->assignRole('ContratistaAdmin');

            $empresaContratista = EmpresaContratista::create([
                'rut_empresa_contratista' => $validatedData['rut_empresa_contratista'],
                'nombre_empresa_contratista' => $validatedData['nombre_empresa_contratista'],
                'razon_social_contratista' => $validatedData['razon_social_contratista'] ?? null,
                'direccion_contratista' => $validatedData['direccion_contratista'] ?? null,
                'ciudad_contratista' => $validatedData['ciudad_contratista'] ?? null,
                'telefono_contratista' => $validatedData['telefono_contratista'] ?? null,
                'email_contratista' => $validatedData['email_contratista'] ?? null,
                'nombre_representante_legal' => $validatedData['nombre_representante_legal'] ?? null,
                'rut_representante_legal' => $validatedData['rut_representante_legal'] ?? null,
                'email_representante_legal' => $validatedData['email_representante_legal'] ?? null,
                'telefono_representante_legal' => $validatedData['telefono_representante_legal'] ?? null,
                'activa' => $validatedData['activa'],
                'user_id' => $user->id,
            ]);

            if (!empty($validatedData['condiciones_ids'])) {
                $empresaContratista->condicionesContratistaMaestro()->sync($validatedData['condiciones_ids']);
            }

            DB::commit();
            $empresaContratista->load(['user', 'condicionesContratistaMaestro']);
            return (new EmpresaContratistaResource($empresaContratista))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store EmpresaContratistaController: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Error al crear la empresa contratista y el usuario asociado.',
                'error' => $e->getMessage() 
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $empresaContratista = EmpresaContratista::with(['user', 'condicionesContratistaMaestro'])->find($id);
        if (!$empresaContratista) {
            return response()->json(['message' => 'Empresa contratista no encontrada.'], Response::HTTP_NOT_FOUND);
        }
        return (new EmpresaContratistaResource($empresaContratista))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $empresaContratista = EmpresaContratista::with('user')->find($id);
        if (!$empresaContratista) {
            return response()->json(['message' => 'Empresa contratista no encontrada.'], Response::HTTP_NOT_FOUND);
        }
        $user = $empresaContratista->user;
        if (!$user) {
            Log::error("Empresa Contratista ID {$empresaContratista->id} no tiene un usuario asociado para actualizar.");
            return response()->json(['message' => 'Error de consistencia: Usuario administrador asociado no encontrado.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $validatedData = $request->validate([
            'rut_empresa_contratista' => [
                'sometimes', 'required', 'string', 'max:20',
                Rule::unique('empresas_contratistas', 'rut_empresa_contratista')
                    ->ignore($empresaContratista->id)
                    ->withoutTrashed(),
            ],
            'nombre_empresa_contratista' => 'sometimes|required|string|max:255',
            'razon_social_contratista' => 'sometimes|nullable|string|max:255',
            'direccion_contratista' => 'sometimes|nullable|string|max:255',
            'ciudad_contratista' => 'sometimes|nullable|string|max:100',
            'telefono_contratista' => 'sometimes|nullable|string|max:50',
            'email_contratista' => 'sometimes|nullable|email|max:255',
            'nombre_representante_legal' => 'sometimes|nullable|string|max:255',
            'rut_representante_legal' => 'sometimes|nullable|string|max:20',
            'email_representante_legal' => 'sometimes|nullable|email|max:255',
            'telefono_representante_legal' => 'sometimes|nullable|string|max:50',
            'activa' => 'sometimes|required|boolean',
            'user_name' => 'sometimes|required|string|max:255',
            'user_email' => [
                'sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'condiciones_ids' => 'sometimes|nullable|array',
            'condiciones_ids.*' => ['integer', Rule::exists('condiciones_contratista_maestro', 'id')->whereNull('deleted_at')],
        ]);

        DB::beginTransaction();
        try {
            $userDataToUpdate = [];
            if ($request->has('user_name')) {
                $userDataToUpdate['name'] = $validatedData['user_name'];
            }
            if ($request->has('user_email')) {
                $userDataToUpdate['email'] = $validatedData['user_email'];
            }
            if (!empty($userDataToUpdate)) {
                $user->update($userDataToUpdate);
            }

            $empresaDataToUpdate = [];
            $empresaFields = [
                'rut_empresa_contratista', 'nombre_empresa_contratista', 'razon_social_contratista',
                'direccion_contratista', 'ciudad_contratista', 'telefono_contratista', 'email_contratista',
                'nombre_representante_legal', 'rut_representante_legal', 'email_representante_legal',
                'telefono_representante_legal', 'activa'
            ];
            foreach($empresaFields as $field) {
                if (array_key_exists($field, $validatedData)) {
                    $empresaDataToUpdate[$field] = $validatedData[$field];
                }
            }
            if (!empty($empresaDataToUpdate)) {
                $empresaContratista->update($empresaDataToUpdate);
            }
            
            if ($request->has('condiciones_ids')) {
                $empresaContratista->condicionesContratistaMaestro()->sync($validatedData['condiciones_ids'] ?? []);
            }

            DB::commit();
            $empresaContratista->load(['user', 'condicionesContratistaMaestro']);
            return (new EmpresaContratistaResource($empresaContratista))
                    ->response()
                    ->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en update EmpresaContratistaController: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Error al actualizar la empresa contratista.',
                'error' => $e->getMessage() 
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $empresaContratista = EmpresaContratista::find($id);

        if (!$empresaContratista) {
            return response()->json(['message' => 'Empresa contratista no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            // Aquí no necesitamos explícitamente marcar 'activa = false' porque
            // el SoftDelete (deleted_at) ya implica que no está activa para las consultas normales.
            // Si tuviéramos lógica específica que dependiera de 'activa' incluso para registros
            // soft-deleted, entonces sí lo haríamos. Por ahora, el delete() es suficiente.

            $empresaContratista->delete(); // Realiza el SoftDelete

            // NOTA SOBRE EL USUARIO:
            // Por ahora, el usuario asociado NO se elimina ni se desactiva.
            // Queda en el sistema. Si la empresa se restaura, el usuario sigue allí.
            // Si se requiere desactivar el usuario, se necesitaría:
            // 1. Un campo 'activo' en el modelo User.
            // 2. Lógica para verificar si el usuario administra OTRAS empresas contratistas.
            //    Si no administra otras, entonces $user->activo = false; $user->save();
            // Esto lo podemos considerar una mejora futura si es necesario.

            DB::commit();

            // Para una operación DELETE exitosa, se devuelve un 204 No Content.
            // No se devuelve cuerpo de respuesta.
            return response()->json(null, Response::HTTP_NO_CONTENT);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en destroy EmpresaContratistaController: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Error al eliminar la empresa contratista.',
                'error' => $e->getMessage() // Solo en desarrollo
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}