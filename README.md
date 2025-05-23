Resumen Actualizado del Proyecto ASEM - API REST (Backend para SPA/Móvil) - (Estado al 22-05-2025 XX:XX)
Instrucción Clave para la IA (al iniciar nuevo chat o continuar): El usuario tiene serios problemas visuales. Este resumen describe el estado actual de un proyecto Laravel API REST que se está construyendo. La IA proporcionará el código completo para cada archivo o modificación necesaria, paso a paso, para que el usuario lo implemente. Este resumen sirve para entender la estructura del proyecto, los componentes creados y el plan de desarrollo.
1. LÓGICA DEL NEGOCIO Y OBJETIVO PRINCIPAL (Sin cambios)
Actores: Empresa Mandante (cliente), Empresas Contratistas (proveedores), ASEM (administrador y validador de la plataforma).
Objetivo ASEM (Plataforma API): Proveer un backend para controlar y validar la documentación de Empresas Contratistas (documentos de la propia empresa, de sus trabajadores, y de sus vehículos) para asegurar el cumplimiento de los requisitos establecidos por cada Empresa Mandante.
Matriz de Exigencias Documentales (Configurada vía API por ASEM ADMIN): La API permitirá a ASEM ADMIN definir, para cada Empresa Mandante, qué documentos se requieren y bajo qué condiciones (Entidad Controlada, Por Cargo, Por Vinculación Jerárquica, Por Condición de la Empresa Contratista, Por Condición del Trabajador).
2. TECNOLOGÍAS Y ARQUITECTURA (Implementada parcialmente)
Backend: Laravel API REST (Versión 12.x).
Autenticación API: Laravel Sanctum (implementado para login/logout con tokens API).
Base de Datos: MySQL (migraciones ejecutadas).
Roles y Permisos: Spatie Laravel Permission (instalado, configurado, roles y permisos base creados vía seeder).
Respuestas API: JSON, utilizando Laravel API Resources (implementados para varios modelos).
Frontend (Separado): No es parte de este backend.
3. ESTRUCTURA DE BASE DE DATOS (Migraciones Creadas y Ejecutadas)
Catálogos universales: tipos_documentos, criterios_evaluacion_maestro, condiciones_contratista_maestro, condiciones_trabajador_maestro.
Entidades: empresas_mandantes, empresas_contratistas, trabajadores, vehiculos, users (Laravel estándar).
Específicas del Mandante (y obligatorias para ellos): cargos, vinculaciones. (Anteriormente podían ser globales, ahora siempre ligadas a un mandante).
Configuración de Reglas: configuraciones_documentos_mandante, configuracion_mandante_criterios.
Documentos Adjuntos: documentos_adjuntos (con relación polimórfica documentable).
Tablas Pivote para relaciones M-M: empresa_contratista_condicion, trabajador_condicion (con campos extra).
Tablas de Auth/Permisos: Tablas de Spatie/laravel-permission.
4. MODELOS ELOQUENT (Creados y Actualizados)
Se han creado modelos Eloquent para todas las tablas mencionadas, definiendo propiedades, $fillable, $casts, relaciones (incluyendo belongsToMany con using y withPivot para Pivots con campos extra) y traits SoftDeletes donde aplica. El modelo User incluye HasApiTokens y HasRoles.
5. SEEDERS (Creados, Ejecutados y Actualizados)
Se han creado y ejecutado seeders para poblar la base de datos con:
Roles (AdminASEM, AnalistaASEM, ContratistaAdmin) y permisos base.
Usuarios de prueba con roles.
Datos de ejemplo para todos los catálogos universales.
Empresas mandantes de ejemplo.
Cargos y vinculaciones de ejemplo (ahora todos asignados a empresas mandantes específicas).
Empresas contratistas de ejemplo, con usuarios administradores asociados y condiciones asignadas.
Trabajadores de ejemplo, con algunas condiciones de trabajador asignadas.
Configuraciones_documentos_mandante de ejemplo (pobladas por el seeder, aunque el CRUD API recién se está implementando).
Configuracion_mandante_criterios de ejemplo.
DatabaseSeeder.php orquesta la ejecución de todos los seeders.
6. SERVICIO DE LÓGICA DE NEGOCIO (Planificado, Aún NO Implementado)
Archivo: app/Services/DocumentRequirementService.php
Propósito: Contendrá la lógica para determinar qué documentos se requieren para una entidad específica (empresa, trabajador, vehículo) basado en las configuraciones_documentos_mandante.
7. PLAN DE DESARROLLO DE ENDPOINTS API (Progreso Actual)
Configuración del Proyecto Base: COMPLETADO.
Autenticación API (Sanctum):
Endpoints para login (emisión de token), logout (revocación de token), y obtener usuario autenticado. COMPLETADO y PROBADO.
CRUDs API (para ASEM ADMIN):
Gestión de CondicionContratistaMaestro: COMPLETADO y PROBADO.
Gestión de TipoDocumento: COMPLETADO y PROBADO.
Gestión de CriteriosEvaluacionMaestro: COMPLETADO y PROBADO.
Gestión de CondicionesTrabajadorMaestro: COMPLETADO y PROBADO.
Gestión de EmpresasMandantes: COMPLETADO y PROBADO.
Gestión de Cargos (siempre asociados a un mandante): COMPLETADO y PROBADO.
Gestión de Vinculaciones (siempre asociadas a un mandante, jerárquicas): COMPLETADO y PROBADO.
Gestión de EmpresasContratistas (con creación de usuario admin y asignación de condiciones): Métodos index, store, show COMPLETADOS y PROBADOS. update, destroy PENDIENTES de implementación completa.
Gestión de ConfiguracionDocumentoMandante (Matriz de Exigencias, con criterios asociados): Métodos index, store, show implementados y en proceso de prueba. update, destroy PENDIENTES de implementación completa.
8. PRÓXIMOS PASOS INMEDIATOS (a continuar en nuevo chat si es necesario)
Completar y probar los métodos update y destroy del CRUD para EmpresasContratistas.
Completar y probar los métodos update y destroy del CRUD para ConfiguracionDocumentoMandante.
Implementación de DocumentRequirementService.php.
Endpoints para los roles ContratistaAdmin (gestión de su propia empresa, trabajadores, vehículos, carga de documentos).
Endpoints para los roles AnalistaASEM (revisión y validación de documentos).
Este resumen debería cubrir el estado actual de manera precisa.

CÓDIGO COMPLETO DE TODOS LOS ARCHIVOS (ÚLTIMA VERSIÓN FUNCIONAL)
(Rutas relativas a la raíz del proyecto Laravel: asem_api)
A. CONTROLADORES (app/Http/Controllers/Api/)
1. app/Http/Controllers/Api/AuthController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Maneja la solicitud de login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'sometimes|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $deviceName = $request->post('device_name', $request->userAgent());
        $token = $user->createToken($deviceName);

        return response()->json([
            'message' => 'Login exitoso.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
        ]);
    }

    /**
     * Maneja la solicitud de logout (revocación del token actual).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout exitoso.']);
    }

    /**
     * Obtener la información del usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
Use code with caution.
PHP
2. app/Http/Controllers/Api/Admin/CondicionContratistaMaestroController.php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CondicionContratistaMaestro;
use Illuminate\Http\Request;
use App\Http\Resources\CondicionContratistaMaestroResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CondicionContratistaMaestroController extends Controller
{
    public function index(): JsonResponse
    {
        $condiciones = CondicionContratistaMaestro::all();
        return CondicionContratistaMaestroResource::collection($condiciones)
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
                Rule::unique('condiciones_contratista_maestro', 'nombre_condicion')->withoutTrashed(),
            ],
            'descripcion_condicion' => 'nullable|string|max:1000',
        ]);

        $condicion = CondicionContratistaMaestro::create($validatedData);

        return (new CondicionContratistaMaestroResource($condicion))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show($id): JsonResponse
    {
        $condicionContratistaMaestro = CondicionContratistaMaestro::find($id);

        if (!$condicionContratistaMaestro) {
            return response()->json(['message' => 'Condición de contratista maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return (new CondicionContratistaMaestroResource($condicionContratistaMaestro))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request, $id): JsonResponse
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
                    ->ignore($condicionContratistaMaestro->id)
                    ->withoutTrashed(),
            ],
            'descripcion_condicion' => 'nullable|string|max:1000',
        ]);

        $condicionContratistaMaestro->update($validatedData);

        return (new CondicionContratistaMaestroResource($condicionContratistaMaestro))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy($id): JsonResponse
    {
        $condicionContratistaMaestro = CondicionContratistaMaestro::find($id);

        if (!$condicionContratistaMaestro) {
            return response()->json(['message' => 'Condición de contratista maestro no encontrada.'], Response::HTTP_NOT_FOUND);
        }
        
        $condicionContratistaMaestro->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
Use code with caution.
PHP
3. app/Http/Controllers/Api/Admin/TipoDocumentoController.php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use App\Http\Resources\TipoDocumentoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TipoDocumentoController extends Controller
{
    public function index(): JsonResponse
    {
        $tiposDocumentos = TipoDocumento::all();
        return TipoDocumentoResource::collection($tiposDocumentos)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

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

    public function show($id): JsonResponse
    {
        $tipoDocumento = TipoDocumento::find($id);

        if (!$tipoDocumento) {
            return response()->json(['message' => 'Tipo de documento no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        return (new TipoDocumentoResource($tipoDocumento))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request, $id): JsonResponse
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

    public function destroy($id): JsonResponse
    {
        $tipoDocumento = TipoDocumento::find($id);

        if (!$tipoDocumento) {
            return response()->json(['message' => 'Tipo de documento no encontrado.'], Response::HTTP_NOT_FOUND);
        }
        
        $tipoDocumento->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
Use code with caution.
PHP
4. app/Http/Controllers/Api/Admin/CriterioEvaluacionMaestroController.php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CriterioEvaluacionMaestro;
use Illuminate\Http\Request;
use App\Http\Resources\CriterioEvaluacionMaestroResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CriterioEvaluacionMaestroController extends Controller
{
    public function index(): JsonResponse
    {
        $criterios = CriterioEvaluacionMaestro::all();
        return CriterioEvaluacionMaestroResource::collection($criterios)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

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

    public function destroy($id): JsonResponse
    {
        $criterio = CriterioEvaluacionMaestro::find($id);

        if (!$criterio) {
            return response()->json(['message' => 'Criterio de evaluación maestro no encontrado.'], Response::HTTP_NOT_FOUND);
        }
        
        $criterio->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
Use code with caution.
PHP
5. app/Http/Controllers/Api/Admin/CondicionTrabajadorMaestroController.php
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
Use code with caution.
PHP
6. app/Http/Controllers/Api/Admin/EmpresaMandanteController.php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpresaMandante;
use Illuminate\Http\Request;
use App\Http\Resources\EmpresaMandanteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class EmpresaMandanteController extends Controller
{
    public function index(): JsonResponse
    {
        $empresas = EmpresaMandante::all();
        return EmpresaMandanteResource::collection($empresas)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'rut_empresa_mandante' => [
                'required',
                'string',
                'max:20',
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

    public function show($id): JsonResponse
    {
        $empresa = EmpresaMandante::find($id);

        if (!$empresa) {
            return response()->json(['message' => 'Empresa mandante no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return (new EmpresaMandanteResource($empresa))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

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

    public function destroy($id): JsonResponse
    {
        $empresa = EmpresaMandante::find($id);

        if (!$empresa) {
            return response()->json(['message' => 'Empresa mandante no encontrada.'], Response::HTTP_NOT_FOUND);
        }
        
        $empresa->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
Use code with caution.
PHP
7. app/Http/Controllers/Api/Admin/CargoController.php
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
                    $empresaMandanteIdParaValidar = $request->has('empresa_mandante_id')
                        ? $request->input('empresa_mandante_id')
                        : $cargo->empresa_mandante_id;
                    
                    if (is_null($empresaMandanteIdParaValidar)) {
                         // Esto no debería ocurrir si empresa_mandante_id es siempre requerido en store
                         // y no se permite cambiar a null. Si se llega aquí, es un caso anómalo.
                         // Considerar lanzar una excepción o error si la lógica de negocio lo requiere.
                         // Por ahora, se asume que $empresaMandanteIdParaValidar no será null
                         // si la entidad ya existe o si se está proveyendo un nuevo ID válido.
                    }
                    return $query->where('empresa_mandante_id', $empresaMandanteIdParaValidar);

                })->withoutTrashed(),
            ],
            'descripcion_cargo' => 'nullable|string|max:1000',
            'empresa_mandante_id' => [
                'sometimes', 
                'required',  
                'integer',
                Rule::exists('empresas_mandantes', 'id')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
                 // Adicionalmente, si la regla de negocio es que la empresa_mandante_id
                 // de un cargo NO PUEDE CAMBIAR una vez creada, se necesitaría una validación custom:
                 function ($attribute, $value, $fail) use ($cargo) {
                     if ($cargo->empresa_mandante_id !== (int)$value) {
                         $fail('No se puede cambiar la empresa mandante de un cargo existente.');
                     }
                 }
            ],
        ]);
        
        $cargo->update($validatedData);
        $cargo->load('empresaMandante'); 

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
        
        $cargo->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
Use code with caution.
PHP
8. app/Http/Controllers/Api/Admin/VinculacionController.php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vinculacion;
use App\Models\EmpresaMandante; 
use Illuminate\Http\Request;
use App\Http\Resources\VinculacionResource; 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; 

class VinculacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vinculacion::query();

        if ($request->has('empresa_mandante_id') && $request->input('empresa_mandante_id') !== null) {
            $request->validate([
                'empresa_mandante_id' => ['required', 'integer', Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at')]
            ]);
            $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'));
        }

        if ($request->boolean('jerarquia_completa') && $request->has('empresa_mandante_id')) {
            $vinculaciones = $query->with(['empresaMandante', 'parent', 'children']) 
                                  ->get();
        } else {
            $vinculaciones = $query->with(['empresaMandante', 'parent'])->get();
        }

        return VinculacionResource::collection($vinculaciones)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_vinculacion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vinculaciones', 'nombre_vinculacion')->where(function ($query) use ($request) {
                    return $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'))
                                 ->where('parent_id', $request->input('parent_id')); 
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
                Rule::exists('vinculaciones', 'id')->where(function ($query) use ($request) {
                    return $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'))
                                 ->whereNull('deleted_at');
                }),
            ],
        ]);

        $vinculacion = Vinculacion::create($validatedData);
        $vinculacion->load(['empresaMandante', 'parent', 'children']);

        return (new VinculacionResource($vinculacion))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show($id): JsonResponse
    {
        $vinculacion = Vinculacion::with(['empresaMandante', 'parent', 'children.children']) 
                               ->find($id);

        if (!$vinculacion) {
            return response()->json(['message' => 'Vinculación no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return (new VinculacionResource($vinculacion))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

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
                    $empresaMandanteIdParaValidar = $request->input('empresa_mandante_id', $vinculacion->empresa_mandante_id);
                    $parentIdParaValidar = $request->input('parent_id', $vinculacion->parent_id);
                    
                    return $query->where('empresa_mandante_id', $empresaMandanteIdParaValidar)
                                 ->where('parent_id', $parentIdParaValidar);
                })->withoutTrashed(),
            ],
            'descripcion_vinculacion' => 'nullable|string|max:1000',
            'empresa_mandante_id' => [ 
                'sometimes',
                'required',
                'integer',
                Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($vinculacion) {
                    if ($vinculacion->empresa_mandante_id !== (int)$value) {
                        $fail('No se puede cambiar la empresa mandante de una vinculación existente.');
                    }
                },
            ],
            'parent_id' => [
                'sometimes', 
                'nullable',
                'integer',
                Rule::exists('vinculaciones', 'id')->where(function ($query) use ($request, $vinculacion) {
                     $empresaMandanteIdDelContexto = $request->input('empresa_mandante_id', $vinculacion->empresa_mandante_id);
                    return $query->where('empresa_mandante_id', $empresaMandanteIdDelContexto)
                                 ->whereNull('deleted_at');
                }),
                Rule::notIn([$vinculacion->id]),
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
                
        $vinculacion->update($validatedData);
        $vinculacion->load(['empresaMandante', 'parent', 'children']);

        return (new VinculacionResource($vinculacion))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy($id): JsonResponse
    {
        $vinculacion = Vinculacion::find($id);

        if (!$vinculacion) {
            return response()->json(['message' => 'Vinculación no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        if ($vinculacion->children()->exists()) {
            return response()->json(['message' => 'No se puede eliminar la vinculación porque tiene vinculaciones hijas asociadas. Elimine o reasigne las hijas primero.'], Response::HTTP_CONFLICT);
        }
        
        $vinculacion->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
Use code with caution.
PHP
9. app/Http/Controllers/Api/Admin/EmpresaContratistaController.php
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
    public function index(): JsonResponse
    {
        $empresas = EmpresaContratista::with(['user', 'condicionesContratistaMaestro'])->get();
        return EmpresaContratistaResource::collection($empresas)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

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

    public function destroy($id): JsonResponse
    {
        $empresaContratista = EmpresaContratista::find($id);

        if (!$empresaContratista) {
            return response()->json(['message' => 'Empresa contratista no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        DB::beginTransaction();
        try {
            $empresaContratista->delete(); 
            DB::commit();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en destroy EmpresaContratistaController: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Error al eliminar la empresa contratista.',
                'error' => $e->getMessage() 
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
Use code with caution.
PHP
10. app/Http/Controllers/Api/Admin/ConfiguracionDocumentoMandanteController.php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionDocumentoMandante;
use App\Models\EmpresaMandante;
use App\Models\TipoDocumento;
use App\Models\Cargo;
use App\Models\Vinculacion;
use App\Models\CondicionContratistaMaestro;
use App\Models\CondicionTrabajadorMaestro;
use App\Models\CriterioEvaluacionMaestro;
use Illuminate\Http\Request;
use App\Http\Resources\ConfiguracionDocumentoMandanteResource; 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ConfiguracionDocumentoMandanteController extends Controller
{
    protected function getRules(Request $request, $configuracionId = null): array
    {
        $entidadControlada = $request->input('entidad_controlada');
        $empresaMandanteId = $request->input('empresa_mandante_id');

        $rules = [
            'empresa_mandante_id' => ['required', 'integer', Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at')],
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documentos', 'id')->whereNull('deleted_at')],
            'entidad_controlada' => ['required', 'string', Rule::in(['EMPRESA', 'TRABAJADOR', 'VEHICULO'])],
            'es_obligatorio' => 'required|boolean',
            'observaciones' => 'nullable|string|max:2000',
            'cargo_id' => [
                Rule::requiredIf(function() use ($entidadControlada, $request) {
                    return $entidadControlada === 'TRABAJADOR' && 
                           empty($request->input('vinculacion_id')) && 
                           empty($request->input('condicion_trabajador_id'));
                }),
                'nullable', 'integer',
                Rule::exists('cargos', 'id')->where(function ($query) use ($empresaMandanteId) {
                     // Cargo debe pertenecer al mandante especificado
                    $query->where('empresa_mandante_id', $empresaMandanteId);
                })->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'TRABAJADOR') {
                        $fail('El campo cargo_id solo es aplicable si la entidad controlada es TRABAJADOR.');
                    }
                },
            ],
            'vinculacion_id' => [
                 Rule::requiredIf(function() use ($entidadControlada, $request) {
                    return $entidadControlada === 'TRABAJADOR' && 
                           empty($request->input('cargo_id')) && 
                           empty($request->input('condicion_trabajador_id'));
                }),
                'nullable', 'integer',
                Rule::exists('vinculaciones', 'id')->where(function ($query) use ($empresaMandanteId) {
                    // Vinculacion debe pertenecer al mandante especificado
                    $query->where('empresa_mandante_id', $empresaMandanteId);
                })->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'TRABAJADOR') {
                        $fail('El campo vinculacion_id solo es aplicable si la entidad controlada es TRABAJADOR.');
                    }
                },
            ],
            'condicion_contratista_id' => [
                Rule::requiredIf(fn() => $entidadControlada === 'EMPRESA' && $request->has('condicion_contratista_id')), // Si se especifica, debe ser requerido.
                'nullable', 'integer',
                Rule::exists('condiciones_contratista_maestro', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'EMPRESA') {
                        $fail('El campo condicion_contratista_id solo es aplicable si la entidad controlada es EMPRESA.');
                    }
                },
            ],
            'condicion_trabajador_id' => [
                Rule::requiredIf(function() use ($entidadControlada, $request) {
                    return $entidadControlada === 'TRABAJADOR' && 
                           empty($request->input('cargo_id')) && 
                           empty($request->input('vinculacion_id'));
                }),
                'nullable', 'integer',
                Rule::exists('condiciones_trabajador_maestro', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'TRABAJADOR') {
                        $fail('El campo condicion_trabajador_id solo es aplicable si la entidad controlada es TRABAJADOR.');
                    }
                },
            ],
            'criterios' => 'nullable|array',
            'criterios.*.criterio_evaluacion_id' => ['required_with:criterios', 'integer', Rule::exists('criterios_evaluacion_maestro', 'id')->whereNull('deleted_at')],
            'criterios.*.es_criterio_obligatorio' => 'required_with:criterios|boolean',
            'criterios.*.instruccion_adicional_criterio' => 'nullable|string|max:1000',
        ];

        $uniqueRule = Rule::unique('configuraciones_documentos_mandante')->where(function ($query) use ($request, $empresaMandanteId, $entidadControlada) {
            $query->where('empresa_mandante_id', $empresaMandanteId)
                  ->where('tipo_documento_id', $request->input('tipo_documento_id'))
                  ->where('entidad_controlada', $entidadControlada)
                  ->where('cargo_id', $request->input('cargo_id')) 
                  ->where('vinculacion_id', $request->input('vinculacion_id')) 
                  ->where('condicion_contratista_id', $request->input('condicion_contratista_id')) 
                  ->where('condicion_trabajador_id', $request->input('condicion_trabajador_id'));
        })->withoutTrashed();

        if ($configuracionId) {
            $uniqueRule->ignore($configuracionId);
        }
        $rules['tipo_documento_id'][] = $uniqueRule;

        if ($entidadControlada === 'TRABAJADOR') {
            $rules['cargo_id'][] = function ($attribute, $value, $fail) use ($request) {
                if (
                    empty($value) && 
                    empty($request->input('vinculacion_id')) &&
                    empty($request->input('condicion_trabajador_id'))
                ) {
                    $fail('Para la entidad TRABAJADOR, debe especificar al menos un Cargo, Vinculación o Condición de Trabajador.');
                }
            };
        }
        return $rules;
    }

    public function index(Request $request): JsonResponse
    {
        $query = ConfiguracionDocumentoMandante::query()->with([
            'empresaMandante:id,nombre_empresa_mandante', 
            'tipoDocumento:id,nombre,es_vencible,requiere_archivo',
            'cargo:id,nombre_cargo',
            'vinculacion:id,nombre_vinculacion',
            'condicionContratistaMaestro:id,nombre_condicion',
            'condicionTrabajadorMaestro:id,nombre_condicion',
            'criteriosEvaluacionMaestro', 
        ]);

        if ($request->has('empresa_mandante_id')) {
            $query->where('empresa_mandante_id', $request->input('empresa_mandante_id'));
        }
        if ($request->has('tipo_documento_id')) {
            $query->where('tipo_documento_id', $request->input('tipo_documento_id'));
        }
        if ($request->has('entidad_controlada')) {
            $query->where('entidad_controlada', $request->input('entidad_controlada'));
        }

        $configuraciones = $query->orderBy('empresa_mandante_id')->orderBy('entidad_controlada')->orderBy('tipo_documento_id')->get();

        return ConfiguracionDocumentoMandanteResource::collection($configuraciones)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate($this->getRules($request));

        $dataToCreate = [
            'empresa_mandante_id' => $validatedData['empresa_mandante_id'],
            'tipo_documento_id' => $validatedData['tipo_documento_id'],
            'entidad_controlada' => $validatedData['entidad_controlada'],
            'es_obligatorio' => $validatedData['es_obligatorio'],
            'observaciones' => $validatedData['observaciones'] ?? null,
        ];

        $entidad = $validatedData['entidad_controlada'];
        $dataToCreate['cargo_id'] = ($entidad === 'TRABAJADOR' && isset($validatedData['cargo_id'])) ? $validatedData['cargo_id'] : null;
        $dataToCreate['vinculacion_id'] = ($entidad === 'TRABAJADOR' && isset($validatedData['vinculacion_id'])) ? $validatedData['vinculacion_id'] : null;
        $dataToCreate['condicion_trabajador_id'] = ($entidad === 'TRABAJADOR' && isset($validatedData['condicion_trabajador_id'])) ? $validatedData['condicion_trabajador_id'] : null;
        $dataToCreate['condicion_contratista_id'] = ($entidad === 'EMPRESA' && isset($validatedData['condicion_contratista_id'])) ? $validatedData['condicion_contratista_id'] : null;

        if ($entidad === 'VEHICULO') {
            $dataToCreate['cargo_id'] = null;
            $dataToCreate['vinculacion_id'] = null;
            $dataToCreate['condicion_trabajador_id'] = null;
            $dataToCreate['condicion_contratista_id'] = null;
        }

        DB::beginTransaction();
        try {
            $configuracion = ConfiguracionDocumentoMandante::create($dataToCreate);

            if (!empty($validatedData['criterios'])) {
                $criteriosParaSincronizar = [];
                foreach ($validatedData['criterios'] as $criterioData) {
                    $criteriosParaSincronizar[$criterioData['criterio_evaluacion_id']] = [
                        'es_criterio_obligatorio' => $criterioData['es_criterio_obligatorio'],
                        'instruccion_adicional_criterio' => $criterioData['instruccion_adicional_criterio'] ?? null,
                    ];
                }
                $configuracion->criteriosEvaluacionMaestro()->sync($criteriosParaSincronizar);
            }

            DB::commit();
            $configuracion->load([
                'empresaMandante:id,nombre_empresa_mandante',
                'tipoDocumento:id,nombre,es_vencible,requiere_archivo',
                'cargo:id,nombre_cargo',
                'vinculacion:id,nombre_vinculacion',
                'condicionContratistaMaestro:id,nombre_condicion',
                'condicionTrabajadorMaestro:id,nombre_condicion',
                'criteriosEvaluacionMaestro',
            ]);
            return (new ConfiguracionDocumentoMandanteResource($configuracion))
                    ->response()
                    ->setStatusCode(Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en store ConfiguracionDocumentoMandanteController: ' . $e->getMessage(), ['exception' => $e, 'request_data' => $request->all()]);
            return response()->json([
                'message' => 'Error al crear la configuración del documento.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id): JsonResponse
    {
        $configuracion = ConfiguracionDocumentoMandante::with([
            'empresaMandante:id,nombre_empresa_mandante',
            'tipoDocumento:id,nombre,es_vencible,requiere_archivo',
            'cargo:id,nombre_cargo',
            'vinculacion:id,nombre_vinculacion',
            'condicionContratistaMaestro:id,nombre_condicion',
            'condicionTrabajadorMaestro:id,nombre_condicion',
            'criteriosEvaluacionMaestro',
        ])->find($id);

        if (!$configuracion) {
            return response()->json(['message' => 'Configuración de documento no encontrada.'], Response::HTTP_NOT_FOUND);
        }
        return (new ConfiguracionDocumentoMandanteResource($configuracion))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Implementación pendiente
        return response()->json(['message' => 'Funcionalidad de actualizar aún no implementada.'], Response::HTTP_NOT_IMPLEMENTED);
    }

    public function destroy($id): JsonResponse
    {
        // Implementación pendiente
        return response()->json(['message' => 'Funcionalidad de eliminar aún no implementada.'], Response::HTTP_NOT_IMPLEMENTED);
    }
}
Use code with caution.
PHP
Como esta respuesta es muy larga, la dividiré. Esta es la PARTE 1 (Controladores).
Perfecto, continuamos con la PARTE 2: API Resources, Rutas, Configuración y Modelos.
B. API RESOURCES (app/Http/Resources/)
1. app/Http/Resources/CondicionContratistaMaestroResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CondicionContratistaMaestroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_condicion' => $this->nombre_condicion,
            'descripcion_condicion' => $this->descripcion_condicion,
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
2. app/Http/Resources/TipoDocumentoResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TipoDocumentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'es_vencible' => (bool) $this->es_vencible,
            'requiere_archivo' => (bool) $this->requiere_archivo,
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
3. app/Http/Resources/CriterioEvaluacionMaestroResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CriterioEvaluacionMaestroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_criterio' => $this->nombre_criterio,
            'descripcion_criterio' => $this->descripcion_criterio,
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
4. app/Http/Resources/CondicionTrabajadorMaestroResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CondicionTrabajadorMaestroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_condicion' => $this->nombre_condicion,
            'descripcion_condicion' => $this->descripcion_condicion,
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
5. app/Http/Resources/EmpresaMandanteResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaMandanteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rut_empresa_mandante' => $this->rut_empresa_mandante,
            'nombre_empresa_mandante' => $this->nombre_empresa_mandante,
            'razon_social_mandante' => $this->razon_social_mandante,
            'direccion_mandante' => $this->direccion_mandante,
            'ciudad_mandante' => $this->ciudad_mandante,
            'telefono_mandante' => $this->telefono_mandante,
            'email_mandante' => $this->email_mandante,
            'nombre_contacto_mandante' => $this->nombre_contacto_mandante,
            'email_contacto_mandante' => $this->email_contacto_mandante,
            'telefono_contacto_mandante' => $this->telefono_contacto_mandante,
            'activa' => (bool) $this->activa,
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'eliminado_en' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
6. app/Http/Resources/CargoResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CargoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_cargo' => $this->nombre_cargo,
            'descripcion_cargo' => $this->descripcion_cargo,
            'empresa_mandante_id' => $this->empresa_mandante_id,
            'empresa_mandante' => $this->whenLoaded('empresaMandante', function () {
                return [
                    'id' => $this->empresaMandante->id,
                    'nombre_empresa_mandante' => $this->empresaMandante->nombre_empresa_mandante,
                ];
            }, null), 
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'eliminado_en' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
7. app/Http/Resources/VinculacionResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VinculacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_vinculacion' => $this->nombre_vinculacion,
            'descripcion_vinculacion' => $this->descripcion_vinculacion,
            'empresa_mandante_id' => $this->empresa_mandante_id,
            'parent_id' => $this->parent_id,
            
            'empresa_mandante' => $this->whenLoaded('empresaMandante', function () {
                return [
                    'id' => $this->empresaMandante->id,
                    'nombre_empresa_mandante' => $this->empresaMandante->nombre_empresa_mandante,
                ];
            }, null),

            'parent' => $this->whenLoaded('parent', function () {
                if ($this->parent && $this->parent->id === $this->id) {
                    return null; 
                }
                return $this->parent ? new VinculacionResource($this->parent) : null;
            }), 

            'children' => $this->whenLoaded('children', function () {
                return VinculacionResource::collection($this->resource->children);
            }),
            
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'eliminado_en' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
8. app/Http/Resources/EmpresaContratistaResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaContratistaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rut_empresa_contratista' => $this->rut_empresa_contratista,
            'nombre_empresa_contratista' => $this->nombre_empresa_contratista,
            'razon_social_contratista' => $this->razon_social_contratista,
            'direccion_contratista' => $this->direccion_contratista,
            'ciudad_contratista' => $this->ciudad_contratista,
            'telefono_contratista' => $this->telefono_contratista,
            'email_contratista' => $this->email_contratista,
            'nombre_representante_legal' => $this->nombre_representante_legal,
            'rut_representante_legal' => $this->rut_representante_legal,
            'email_representante_legal' => $this->email_representante_legal,
            'telefono_representante_legal' => $this->telefono_representante_legal,
            'activa' => (bool) $this->activa,
            'user_id' => $this->user_id,

            'usuario_administrador' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'roles' => $this->user->getRoleNames(), 
                ];
            }, null),

            'condiciones_contratista' => $this->whenLoaded('condicionesContratistaMaestro', function () {
                return CondicionContratistaMaestroResource::collection($this->condicionesContratistaMaestro);
            }),
            
            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'eliminado_en' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
9. app/Http/Resources/ConfiguracionDocumentoMandanteResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfiguracionDocumentoMandanteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'empresa_mandante_id' => $this->empresa_mandante_id,
            'tipo_documento_id' => $this->tipo_documento_id,
            'entidad_controlada' => $this->entidad_controlada,
            'cargo_id' => $this->cargo_id,
            'vinculacion_id' => $this->vinculacion_id,
            'condicion_contratista_id' => $this->condicion_contratista_id,
            'condicion_trabajador_id' => $this->condicion_trabajador_id,
            'es_obligatorio' => (bool) $this->es_obligatorio,
            'observaciones' => $this->observaciones,

            'empresa_mandante' => $this->whenLoaded('empresaMandante', function () {
                return [
                    'id' => $this->empresaMandante->id,
                    'nombre_empresa_mandante' => $this->empresaMandante->nombre_empresa_mandante,
                ];
            }),
            'tipo_documento' => $this->whenLoaded('tipoDocumento', function () {
                return [
                    'id' => $this->tipoDocumento->id,
                    'nombre' => $this->tipoDocumento->nombre,
                    'es_vencible' => (bool) $this->tipoDocumento->es_vencible,
                    'requiere_archivo' => (bool) $this->tipoDocumento->requiere_archivo,
                ];
            }),
            'cargo' => $this->whenLoaded('cargo', function () {
                return $this->cargo ? [ // Verificar si $this->cargo no es null
                    'id' => $this->cargo->id,
                    'nombre_cargo' => $this->cargo->nombre_cargo,
                ] : null;
            }),
            'vinculacion' => $this->whenLoaded('vinculacion', function () {
                return $this->vinculacion ? [ // Verificar si $this->vinculacion no es null
                    'id' => $this->vinculacion->id,
                    'nombre_vinculacion' => $this->vinculacion->nombre_vinculacion,
                ] : null;
            }),
            'condicion_contratista' => $this->whenLoaded('condicionContratistaMaestro', function () {
                return $this->condicionContratistaMaestro ? [
                    'id' => $this->condicionContratistaMaestro->id,
                    'nombre_condicion' => $this->condicionContratistaMaestro->nombre_condicion,
                ] : null;
            }),
            'condicion_trabajador' => $this->whenLoaded('condicionTrabajadorMaestro', function () {
                return $this->condicionTrabajadorMaestro ? [
                    'id' => $this->condicionTrabajadorMaestro->id,
                    'nombre_condicion' => $this->condicionTrabajadorMaestro->nombre_condicion,
                ] : null;
            }),

            'criterios_evaluacion' => $this->whenLoaded('criteriosEvaluacionMaestro', function () {
                return $this->criteriosEvaluacionMaestro->map(function ($criterio) {
                    return [
                        'id_criterio_maestro' => $criterio->id,
                        'nombre_criterio' => $criterio->nombre_criterio,
                        'descripcion_criterio' => $criterio->descripcion_criterio,
                        'pivot_id_configuracion_criterio' => $criterio->pivot->id, 
                        'pivot_es_criterio_obligatorio' => (bool) $criterio->pivot->es_criterio_obligatorio,
                        'pivot_instruccion_adicional_criterio' => $criterio->pivot->instruccion_adicional_criterio,
                    ];
                });
            }),

            'creado_en' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'actualizado_en' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'eliminado_en' => $this->deleted_at ? $this->deleted_at->toIso8601String() : null,
        ];
    }
}
Use code with caution.
PHP
C. RUTAS (routes/)
1. routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CondicionContratistaMaestroController;
use App\Http\Controllers\Api\Admin\TipoDocumentoController;
use App\Http\Controllers\Api\Admin\CriterioEvaluacionMaestroController;
use App\Http\Controllers\Api\Admin\CondicionTrabajadorMaestroController;
use App\Http\Controllers\Api\Admin\EmpresaMandanteController;
use App\Http\Controllers\Api\Admin\CargoController;
use App\Http\Controllers\Api\Admin\VinculacionController;
use App\Http\Controllers\Api\Admin\EmpresaContratistaController;
use App\Http\Controllers\Api\Admin\ConfiguracionDocumentoMandanteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    Route::middleware(['role:AdminASEM'])->prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('condiciones-contratista-maestro', CondicionContratistaMaestroController::class);
        Route::apiResource('tipos-documentos', TipoDocumentoController::class);
        Route::apiResource('criterios-evaluacion-maestro', CriterioEvaluacionMaestroController::class);
        Route::apiResource('condiciones-trabajador-maestro', CondicionTrabajadorMaestroController::class);
        Route::apiResource('empresas-mandantes', EmpresaMandanteController::class);
        Route::apiResource('cargos', CargoController::class);
        Route::apiResource('vinculaciones', VinculacionController::class);
        Route::apiResource('empresas-contratistas', EmpresaContratistaController::class);
        Route::apiResource('configuraciones-documentos-mandante', ConfiguracionDocumentoMandanteController::class);
    });
});

Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando sin autenticación']);
});
Use code with caution.
PHP
D. CONFIGURACIÓN (bootstrap/ y config/)
1. bootstrap/app.php (solo la sección withMiddleware relevante, el resto es estándar de Laravel 11)
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Esto es para la API, si tienes una política global de CORS
        // $middleware->group('api', [
        //     \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        //     'throttle:api',
        //     \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
Use code with caution.
PHP
2. config/cors.php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'], // AJUSTA ESTO A TU FRONTEND
    // 'allowed_origins' => ['*'], // Solo para desarrollo si es estrictamente necesario
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Importante para Sanctum con cookies si se usa (no tanto para tokens API)
];
Use code with caution.
PHP
Esto completa la Parte 2. 
E. MODELOS (app/Models/)
(Te proporciono los modelos más relevantes o aquellos que han tenido modificaciones significativas. Los que solo se crearon con migraciones y no hemos tocado para CRUDs específicos aún, como Vehiculo o DocumentoAdjunto, se mantienen como estaban, pero los incluiré para que tengas el set completo).
1. app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // Asegúrate que esto esté

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // Y aquí

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relación: Un usuario puede administrar una empresa contratista
    public function empresaContratistaAdministrada()
    {
        return $this->hasOne(EmpresaContratista::class, 'user_id');
    }

    // Relación: Documentos subidos por este usuario
    public function documentosSubidos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'subido_por_user_id');
    }

    // Relación: Documentos validados por este usuario
    public function documentosValidados()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'validado_por_user_id');
    }
}
Use code with caution.
PHP
2. app/Models/TipoDocumento.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoDocumento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_documentos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_vencible',
        'requiere_archivo',
    ];

    protected $casts = [
        'es_vencible' => 'boolean',
        'requiere_archivo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function configuracionesDocumentosMandante()
    {
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'tipo_documento_id');
    }

    public function documentosAdjuntos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'tipo_documento_id');
    }
}
Use code with caution.
PHP
3. app/Models/CriterioEvaluacionMaestro.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CriterioEvaluacionMaestro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'criterios_evaluacion_maestro';

    protected $fillable = [
        'nombre_criterio',
        'descripcion_criterio',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function configuracionesDocumentosMandante()
    {
        return $this->belongsToMany(
            ConfiguracionDocumentoMandante::class,
            'configuracion_mandante_criterios', // Nombre de la tabla pivote
            'criterio_evaluacion_id',           // Clave foránea en la tabla pivote para este modelo
            'configuracion_documento_id'        // Clave foránea en la tabla pivote para el modelo relacionado
        )
        ->using(ConfiguracionMandanteCriterio::class) // Especifica el modelo Pivot personalizado
        ->withPivot(['id', 'es_criterio_obligatorio', 'instruccion_adicional_criterio']) // Campos extra en la pivote
        ->withTimestamps(); // Si la tabla pivote tiene timestamps
    }
}
Use code with caution.
PHP
4. app/Models/CondicionContratistaMaestro.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CondicionContratistaMaestro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'condiciones_contratista_maestro';

    protected $fillable = [
        'nombre_condicion',
        'descripcion_condicion',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function empresasContratistas()
    {
        return $this->belongsToMany(
            EmpresaContratista::class,
            'empresa_contratista_condicion',      // Tabla pivote
            'condicion_contratista_id',           // FK en pivote para este modelo
            'empresa_contratista_id'              // FK en pivote para el modelo relacionado
        )->withTimestamps(); // Asumiendo que la tabla pivote tiene timestamps
    }

    public function configuracionesDocumentosMandante()
    {
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'condicion_contratista_id');
    }
}
Use code with caution.
PHP
5. app/Models/CondicionTrabajadorMaestro.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CondicionTrabajadorMaestro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'condiciones_trabajador_maestro';

    protected $fillable = [
        'nombre_condicion',
        'descripcion_condicion',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function trabajadores()
    {
        return $this->belongsToMany(
            Trabajador::class,
            'trabajador_condicion',             // Tabla pivote
            'condicion_trabajador_id',          // FK en pivote para este modelo
            'trabajador_id'                     // FK en pivote para el modelo relacionado
        )
        ->using(TrabajadorCondicion::class)     // Especifica el modelo Pivot personalizado
        ->withPivot(['fecha_asignacion_condicion', 'fecha_vencimiento_condicion', 'valor_extra_condicion']) // Campos extra
        ->withTimestamps();
    }

    public function configuracionesDocumentosMandante()
    {
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'condicion_trabajador_id');
    }
}
Use code with caution.
PHP
6. app/Models/EmpresaMandante.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaMandante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empresas_mandantes';

    protected $fillable = [
        'rut_empresa_mandante',
        'nombre_empresa_mandante',
        'razon_social_mandante',
        'direccion_mandante',
        'ciudad_mandante',
        'telefono_mandante',
        'email_mandante',
        'nombre_contacto_mandante',
        'email_contacto_mandante',
        'telefono_contacto_mandante',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function cargos()
    {
        // Un mandante puede tener muchos cargos específicos para él
        return $this->hasMany(Cargo::class, 'empresa_mandante_id');
    }

    public function vinculaciones()
    {
        // Un mandante puede tener muchas vinculaciones específicas para él
        return $this->hasMany(Vinculacion::class, 'empresa_mandante_id');
    }

    public function configuracionesDocumentosMandante()
    {
        // Un mandante define muchas configuraciones de documentos
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'empresa_mandante_id');
    }
}
Use code with caution.
PHP
7. app/Models/Cargo.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cargos';

    protected $fillable = [
        'empresa_mandante_id', // Ahora siempre requerido
        'nombre_cargo',
        'descripcion_cargo',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function empresaMandante()
    {
        // Un cargo pertenece a una EmpresaMandante (o es global si empresa_mandante_id es null)
        // SEGÚN NUESTRA ÚLTIMA REGLA, empresa_mandante_id NUNCA es null para Cargo.
        return $this->belongsTo(EmpresaMandante::class, 'empresa_mandante_id');
    }

    public function configuracionesDocumentosMandante()
    {
        // Un cargo puede estar referenciado en muchas configuraciones de documentos
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'cargo_id');
    }
}
Use code with caution.
PHP
8. app/Models/Vinculacion.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vinculacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vinculaciones';

    protected $fillable = [
        'empresa_mandante_id', // Ahora siempre requerido
        'nombre_vinculacion',
        'descripcion_vinculacion',
        'parent_id', // Para la auto-referencia jerárquica
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function empresaMandante()
    {
        // Una vinculación pertenece a una EmpresaMandante.
        return $this->belongsTo(EmpresaMandante::class, 'empresa_mandante_id');
    }

    // Relación padre (auto-referencia)
    public function parent()
    {
        return $this->belongsTo(Vinculacion::class, 'parent_id');
    }

    // Relación hijos (auto-referencia)
    public function children()
    {
        return $this->hasMany(Vinculacion::class, 'parent_id');
    }

    public function configuracionesDocumentosMandante()
    {
        // Una vinculación puede estar referenciada en muchas configuraciones de documentos
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'vinculacion_id');
    }
}
Use code with caution.
PHP
9. app/Models/EmpresaContratista.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaContratista extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empresas_contratistas';

    protected $fillable = [
        'rut_empresa_contratista',
        'nombre_empresa_contratista',
        'razon_social_contratista',
        'direccion_contratista',
        'ciudad_contratista',
        'telefono_contratista',
        'email_contratista',
        'nombre_representante_legal',
        'rut_representante_legal',
        'email_representante_legal',
        'telefono_representante_legal',
        'activa',
        'user_id', // FK para el usuario administrador de esta contratista
    ];

    protected $casts = [
        'activa' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relación: Una empresa contratista es administrada por un Usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: Una empresa contratista tiene muchos Trabajadores
    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'empresa_contratista_id');
    }

    // Relación: Una empresa contratista tiene muchos Vehiculos
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'empresa_contratista_id');
    }

    // Relación: Una empresa contratista puede tener muchas CondicionesContratistaMaestro
    public function condicionesContratistaMaestro()
    {
        return $this->belongsToMany(
            CondicionContratistaMaestro::class,
            'empresa_contratista_condicion',  // Tabla pivote
            'empresa_contratista_id',         // FK en pivote para este modelo
            'condicion_contratista_id'        // FK en pivote para el modelo relacionado
        )->withTimestamps(); // Si la tabla pivote tiene timestamps
    }

    // Relación Polimórfica: Una empresa contratista puede tener muchos documentos adjuntos
    public function documentosAdjuntos()
    {
        return $this->morphMany(DocumentoAdjunto::class, 'documentable');
    }
}
Use code with caution.
PHP
10. app/Models/Trabajador.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trabajadores';

    protected $fillable = [
        'empresa_contratista_id',
        'rut_trabajador',
        'nombres_trabajador',
        'apellido_paterno_trabajador',
        'apellido_materno_trabajador',
        'fecha_nacimiento_trabajador',
        'nacionalidad_trabajador',
        'telefono_trabajador',
        'email_trabajador',
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento_trabajador' => 'date',
        'activo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function empresaContratista()
    {
        return $this->belongsTo(EmpresaContratista::class, 'empresa_contratista_id');
    }

    public function condicionesTrabajadorMaestro()
    {
        return $this->belongsToMany(
            CondicionTrabajadorMaestro::class,
            'trabajador_condicion',
            'trabajador_id',
            'condicion_trabajador_id'
        )
        ->using(TrabajadorCondicion::class)
        ->withPivot(['fecha_asignacion_condicion', 'fecha_vencimiento_condicion', 'valor_extra_condicion'])
        ->withTimestamps();
    }

    public function documentosAdjuntos()
    {
        return $this->morphMany(DocumentoAdjunto::class, 'documentable');
    }
}
Use code with caution.
PHP
11. app/Models/Vehiculo.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehiculos';

    protected $fillable = [
        'empresa_contratista_id',
        'patente',
        'marca',
        'modelo',
        'ano',
        'tipo_vehiculo', // Podría ser un catálogo: Camioneta, Camión, Auto, Maquinaria, etc.
        'numero_motor',
        'numero_chasis',
        'fecha_adquisicion',
        'activo',
    ];

    protected $casts = [
        'ano' => 'integer',
        'fecha_adquisicion' => 'date',
        'activo' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function empresaContratista()
    {
        return $this->belongsTo(EmpresaContratista::class, 'empresa_contratista_id');
    }

    public function documentosAdjuntos()
    {
        return $this->morphMany(DocumentoAdjunto::class, 'documentable');
    }
}
Use code with caution.
PHP
12. app/Models/ConfiguracionDocumentoMandante.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfiguracionDocumentoMandante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'configuraciones_documentos_mandante';

    protected $fillable = [
        'empresa_mandante_id',
        'tipo_documento_id',
        'entidad_controlada', // EMPRESA, TRABAJADOR, VEHICULO
        'cargo_id', // FK a cargos (nullable)
        'vinculacion_id', // FK a vinculaciones (nullable)
        'condicion_contratista_id', // FK a condiciones_contratista_maestro (nullable)
        'condicion_trabajador_id', // FK a condiciones_trabajador_maestro (nullable)
        'es_obligatorio',
        'observaciones',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // Relaciones BelongsTo
    public function empresaMandante()
    {
        return $this->belongsTo(EmpresaMandante::class, 'empresa_mandante_id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function vinculacion()
    {
        return $this->belongsTo(Vinculacion::class, 'vinculacion_id');
    }

    public function condicionContratistaMaestro()
    {
        return $this->belongsTo(CondicionContratistaMaestro::class, 'condicion_contratista_id');
    }

    public function condicionTrabajadorMaestro()
    {
        return $this->belongsTo(CondicionTrabajadorMaestro::class, 'condicion_trabajador_id');
    }

    // Relación Muchos-a-Muchos con CriterioEvaluacionMaestro
    public function criteriosEvaluacionMaestro()
    {
        return $this->belongsToMany(
            CriterioEvaluacionMaestro::class,
            'configuracion_mandante_criterios',     // Nombre de la tabla pivote
            'configuracion_documento_id',           // FK en pivote para este modelo
            'criterio_evaluacion_id'                // FK en pivote para el modelo CriterioEvaluacionMaestro
        )
        ->using(ConfiguracionMandanteCriterio::class) // Especifica el modelo Pivot personalizado
        ->withPivot(['id', 'es_criterio_obligatorio', 'instruccion_adicional_criterio']) // Campos extra en la pivote
        ->withTimestamps(); // Si la tabla pivote tiene timestamps
    }

    // Relación con DocumentosAdjuntos (si un documento se adjunta cumpliendo esta configuración)
    public function documentosAdjuntos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'configuracion_documento_mandante_id');
    }
}
Use code with caution.
PHP
13. app/Models/DocumentoAdjunto.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoAdjunto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documentos_adjuntos';

    protected $fillable = [
        'tipo_documento_id', // Qué tipo de documento es (cédula, contrato, etc.)
        'documentable_id',   // ID de la entidad a la que pertenece (EmpresaContratista, Trabajador, Vehiculo)
        'documentable_type', // Clase de la entidad (App\Models\EmpresaContratista, etc.)
        'subido_por_user_id', // Quién subió el documento (User ID)
        'nombre_original_archivo',
        'path_archivo', // Path en el storage
        'mime_type',
        'tamano_archivo', // en bytes
        'estado_validacion', // PENDIENTE, APROBADO, RECHAZADO, VENCIDO
        'observaciones_validacion',
        'fecha_emision_documento', // Si aplica
        'fecha_vencimiento_documento', // Si aplica (para documentos vencibles)
        'validado_por_user_id', // Quién validó/rechazó (User ID de ASEM)
        'fecha_validacion',
        'configuracion_documento_mandante_id', // Opcional: ID de la regla que originó la necesidad de este documento
    ];

    protected $casts = [
        'fecha_emision_documento' => 'date',
        'fecha_vencimiento_documento' => 'date',
        'fecha_validacion' => 'datetime',
        'tamano_archivo' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // Relación con TipoDocumento
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    // Relación Polimórfica: Documentable (puede ser EmpresaContratista, Trabajador, Vehiculo)
    public function documentable()
    {
        return $this->morphTo();
    }

    // Usuario que subió el documento
    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por_user_id');
    }

    // Usuario que validó el documento
    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por_user_id');
    }

    // Configuración que originó este documento (opcional)
    public function configuracionDocumentoMandante()
    {
        return $this->belongsTo(ConfiguracionDocumentoMandante::class, 'configuracion_documento_mandante_id');
    }
}
Use code with caution.
PHP
Modelos Pivot Personalizados (app/Models/)
14. app/Models/TrabajadorCondicion.php (Extiende Pivot)
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TrabajadorCondicion extends Pivot
{
    protected $table = 'trabajador_condicion';
    public $incrementing = true; // Si tu PK es 'id' y autoincremental
    public $timestamps = true; // Si tienes created_at y updated_at en la tabla pivote

    protected $fillable = [
        'trabajador_id',
        'condicion_trabajador_id',
        'fecha_asignacion_condicion',
        'fecha_vencimiento_condicion',
        'valor_extra_condicion', // Campo de ejemplo, puede ser JSON o string
    ];

    protected $casts = [
        'fecha_asignacion_condicion' => 'date',
        'fecha_vencimiento_condicion' => 'date',
    ];

    // Opcional: definir relaciones inversas desde el Pivot si necesitas acceder
    // directamente al trabajador o a la condición desde una instancia del Pivot.
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function condicionTrabajadorMaestro()
    {
        return $this->belongsTo(CondicionTrabajadorMaestro::class, 'condicion_trabajador_id');
    }
}
Use code with caution.
PHP
15. app/Models/ConfiguracionMandanteCriterio.php (Extiende Pivot)
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ConfiguracionMandanteCriterio extends Pivot
{
    protected $table = 'configuracion_mandante_criterios';
    public $incrementing = true; // PK es 'id'
    public $timestamps = true;

    protected $fillable = [
        'configuracion_documento_id',
        'criterio_evaluacion_id',
        'es_criterio_obligatorio',
        'instruccion_adicional_criterio',
    ];

    protected $casts = [
        'es_criterio_obligatorio' => 'boolean',
    ];

    // Opcional: definir relaciones inversas desde el Pivot
    public function configuracionDocumentoMandante()
    {
        return $this->belongsTo(ConfiguracionDocumentoMandante::class, 'configuracion_documento_id');
    }

    public function criterioEvaluacionMaestro()
    {
        return $this->belongsTo(CriterioEvaluacionMaestro::class, 'criterio_evaluacion_id');
    }
}
Use code with caution.
PHP
(He añadido public $incrementing = true; a los modelos Pivot ya que tienen una PK id)
Esto completa la sección de Modelos. En la siguiente y última parte (Parte 4), te proporcionaré los Seeders. Por favor, confirma que estás listo.
¡Perfecto! Vamos con la PARTE 4 y Final: Seeders.
F. SEEDERS (database/seeders/)
1. database/seeders/DatabaseSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Desactivar revisión de claves foráneas para MySQL/MariaDB
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Llamar a los seeders en orden de dependencia
        $this->call([
            RoleAndPermissionSeeder::class,       // Roles y permisos primero
            UserSeeder::class,                    // Usuarios que usarán roles
            CatalogSeeder::class,                 // Catálogos base (TipoDocumento, Criterios, Condiciones Maestro)
            EmpresaMandanteSeeder::class,         // Empresas Mandantes
            CargoVinculacionSeeder::class,        // Cargos y Vinculaciones (dependen de Mandantes)
            EmpresaContratistaSeeder::class,      // Empresas Contratistas (dependen de User y Condiciones)
            TrabajadorSeeder::class,              // Trabajadores (dependen de Contratistas y Condiciones)
            // VehiculoSeeder::class,             // (Si tuvieras un seeder para Vehículos)
            ConfiguracionDocumentoMandanteSeeder::class, // Configuraciones (dependen de muchos catálogos y entidades)
            ConfiguracionMandanteCriterioSeeder::class,  // Criterios para Configuraciones (depende de Configuraciones y Criterios Maestro)
            // DocumentoAdjuntoSeeder::class      // (Si tuvieras un seeder para Documentos Adjuntos de ejemplo)
        ]);

        // Reactivar revisión de claves foráneas
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->command->info('Base de datos poblada exitosamente con todos los seeders.');
    }
}
Use code with caution.
PHP
2. database/seeders/RoleAndPermissionSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
// No necesitas `use App\Models\User;` aquí

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear Permisos
        Permission::firstOrCreate(['name' => 'administrar plataforma']); // Permiso general para AdminASEM
        Permission::firstOrCreate(['name' => 'gestionar tipos_documentos']);
        Permission::firstOrCreate(['name' => 'gestionar criterios_evaluacion']);
        Permission::firstOrCreate(['name' => 'gestionar condiciones_maestro']); // Genérico para ambas condiciones maestro
        Permission::firstOrCreate(['name' => 'gestionar empresas_mandantes']);
        Permission::firstOrCreate(['name' => 'gestionar empresas_contratistas_plataforma']); // AdminASEM gestiona todas
        Permission::firstOrCreate(['name' => 'gestionar cargos_plataforma']); // AdminASEM gestiona todos los cargos
        Permission::firstOrCreate(['name' => 'gestionar vinculaciones_plataforma']); // AdminASEM gestiona todas las vinculaciones
        Permission::firstOrCreate(['name' => 'gestionar matriz_exigencias']); // Para ConfiguracionDocumentoMandante

        Permission::firstOrCreate(['name' => 'revisar documentos_pendientes']); // AnalistaASEM
        Permission::firstOrCreate(['name' => 'validar documentos']); // AnalistaASEM
        Permission::firstOrCreate(['name' => 'rechazar documentos']); // AnalistaASEM

        Permission::firstOrCreate(['name' => 'gestionar mi_empresa_contratista']); // ContratistaAdmin
        Permission::firstOrCreate(['name' => 'gestionar mis_trabajadores']); // ContratistaAdmin
        Permission::firstOrCreate(['name' => 'gestionar mis_vehiculos']); // ContratistaAdmin
        Permission::firstOrCreate(['name' => 'cargar mis_documentos']); // ContratistaAdmin
        Permission::firstOrCreate(['name' => 'ver mis_documentos_requeridos']); // ContratistaAdmin

        // Crear Roles
        $roleAdminAsem = Role::firstOrCreate(['name' => 'AdminASEM']);
        $roleAnalistaAsem = Role::firstOrCreate(['name' => 'AnalistaASEM']);
        $roleContratistaAdmin = Role::firstOrCreate(['name' => 'ContratistaAdmin']);

        // Asignar Permisos a Roles
        $roleAdminAsem->givePermissionTo([
            'administrar plataforma',
            'gestionar tipos_documentos',
            'gestionar criterios_evaluacion',
            'gestionar condiciones_maestro',
            'gestionar empresas_mandantes',
            'gestionar empresas_contratistas_plataforma',
            'gestionar cargos_plataforma',
            'gestionar vinculaciones_plataforma',
            'gestionar matriz_exigencias',
            'revisar documentos_pendientes', // Podría tenerlo también para supervisión
            'validar documentos',           // Podría tenerlo también
            'rechazar documentos',          // Podría tenerlo también
        ]);

        $roleAnalistaAsem->givePermissionTo([
            'revisar documentos_pendientes',
            'validar documentos',
            'rechazar documentos',
        ]);

        $roleContratistaAdmin->givePermissionTo([
            'gestionar mi_empresa_contratista',
            'gestionar mis_trabajadores',
            'gestionar mis_vehiculos',
            'cargar mis_documentos',
            'ver mis_documentos_requeridos',
        ]);

        $this->command->info('Roles y Permisos creados y asignados exitosamente.');
    }
}
Use code with caution.
PHP
3. database/seeders/UserSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Asegúrate de importar Role
use Illuminate\Support\Facades\Log;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- UserSeeder: Iniciando ---'); 
        Log::info('--- UserSeeder: Iniciando ---');

        // Admin ASEM
        $adminAsem = User::firstOrCreate(
            ['email' => 'admin.asem@example.com'],
            [
                'name' => 'Administrador ASEM Plataforma',
                'password' => Hash::make('password'), // Cambiar en producción
            ]
        );
        if ($adminAsem->wasRecentlyCreated) {
            $this->command->info('UserSeeder: Admin ASEM CREADO.');
            Log::info('UserSeeder: Admin ASEM CREADO con ID: ' . $adminAsem->id);
        } else {
            $this->command->info('UserSeeder: Admin ASEM YA EXISTÍA.');
            Log::info('UserSeeder: Admin ASEM YA EXISTÍA con ID: ' . $adminAsem->id);
        }

        $roleAdminAsem = Role::where('name', 'AdminASEM')->first();
        if ($adminAsem && $roleAdminAsem) {
            $adminAsem->assignRole($roleAdminAsem);
            $this->command->info('UserSeeder: Rol AdminASEM asignado a Admin ASEM.');
            Log::info('UserSeeder: Rol AdminASEM asignado a Admin ASEM.');
        } else {
            $errorMessage = 'UserSeeder: ';
            if (!$adminAsem) $errorMessage .= 'Usuario adminAsem no pudo ser creado/encontrado. ';
            if (!$roleAdminAsem) $errorMessage .= 'Rol AdminASEM NO ENCONTRADO. ';
            $this->command->error($errorMessage);
            Log::error($errorMessage);
        }
        
        // Analista ASEM
        $analistaAsem = User::firstOrCreate(
            ['email' => 'analista.asem@example.com'],
            [
                'name' => 'Analista ASEM Validador',
                'password' => Hash::make('password'), // Cambiar en producción
            ]
        );
         if ($analistaAsem->wasRecentlyCreated) {
            $this->command->info('UserSeeder: Analista ASEM CREADO.');
        } else {
            $this->command->info('UserSeeder: Analista ASEM YA EXISTÍA.');
        }
        $roleAnalistaAsem = Role::where('name', 'AnalistaASEM')->first();
        if ($analistaAsem && $roleAnalistaAsem) {
            $analistaAsem->assignRole($roleAnalistaAsem);
        }

        // Los usuarios para ContratistaAdmin se crean junto con sus empresas en EmpresaContratistaSeeder.
        // Sin embargo, podemos dejar estos como ejemplo si se crean manualmente antes de las empresas.
        // Si EmpresaContratistaSeeder los crea, estos no se usarán o se podrían omitir aquí.

        $contratistaAdminUser1 = User::firstOrCreate(
            ['email' => 'contratista.admin1@example.com'],
            [
                'name' => 'Admin Contratista Uno (Pre-creado)',
                'password' => Hash::make('password'),
            ]
        );
        $roleContratistaAdmin = Role::where('name', 'ContratistaAdmin')->first();
        if ($contratistaAdminUser1 && $roleContratistaAdmin) {
            $contratistaAdminUser1->assignRole($roleContratistaAdmin);
        }


        $contratistaAdminUser2 = User::firstOrCreate(
            ['email' => 'contratista.admin2@example.com'],
            [
                'name' => 'Admin Contratista Dos (Pre-creado)',
                'password' => Hash::make('password'),
            ]
        );
        if ($contratistaAdminUser2 && $roleContratistaAdmin) {
            $contratistaAdminUser2->assignRole($roleContratistaAdmin);
        }


        $this->command->info('--- UserSeeder: Finalizado ---');
        Log::info('--- UserSeeder: Finalizado. ---');
    }
}
Use code with caution.
PHP
4. database/seeders/CatalogSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;
use App\Models\CriterioEvaluacionMaestro;
use App\Models\CondicionContratistaMaestro;
use App\Models\CondicionTrabajadorMaestro;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Truncar en orden inverso de dependencia o manejar FKs
        DB::table('configuracion_mandante_criterios')->truncate(); // Depende de criterios y config_docs
        DB::table('configuraciones_documentos_mandante')->truncate(); // Depende de tipos_docs, condiciones, etc.
        
        TipoDocumento::truncate();
        CriterioEvaluacionMaestro::truncate();
        CondicionContratistaMaestro::truncate();
        CondicionTrabajadorMaestro::truncate();

        // Tipos de Documento
        TipoDocumento::create(['nombre' => 'Cédula de Identidad', 'descripcion' => 'Documento Nacional de Identificación.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 1
        TipoDocumento::create(['nombre' => 'Licencia de Conducir', 'descripcion' => 'Autorización para conducir vehículos.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 2
        TipoDocumento::create(['nombre' => 'Certificado de Antecedentes', 'descripcion' => 'Certificado de antecedentes penales.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 3
        TipoDocumento::create(['nombre' => 'Contrato de Trabajo', 'descripcion' => 'Documento legal de vinculación laboral.', 'es_vencible' => false, 'requiere_archivo' => true]); // ID: 4
        TipoDocumento::create(['nombre' => 'Certificado AFP', 'descripcion' => 'Certificado de cotizaciones previsionales.', 'es_vencible' => false, 'requiere_archivo' => true]); // ID: 5
        TipoDocumento::create(['nombre' => 'Certificado Salud', 'descripcion' => 'Certificado de cotizaciones de salud.', 'es_vencible' => false, 'requiere_archivo' => true]); // ID: 6
        TipoDocumento::create(['nombre' => 'Examen de Altura Física', 'descripcion' => 'Certifica aptitud para trabajos en altura.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 7
        TipoDocumento::create(['nombre' => 'Permiso de Circulación', 'descripcion' => 'Documento vehicular obligatorio.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 8
        TipoDocumento::create(['nombre' => 'Seguro Obligatorio (SOAP)', 'descripcion' => 'Seguro obligatorio de accidentes personales para vehículos.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 9
        TipoDocumento::create(['nombre' => 'Revisión Técnica Vehicular', 'descripcion' => 'Certificado de inspección técnica de vehículos.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 10
        TipoDocumento::create(['nombre' => 'Escritura de Constitución Empresa', 'descripcion' => 'Documento legal de creación de la empresa.', 'es_vencible' => false, 'requiere_archivo' => true]); // ID: 11
        TipoDocumento::create(['nombre' => 'Certificado Vigencia Empresa', 'descripcion' => 'Certificado de vigencia de la sociedad.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 12
        TipoDocumento::create(['nombre' => 'Certificado Curso de Altura', 'descripcion' => 'Certificado de aprobación de curso de trabajo seguro en altura.', 'es_vencible' => true, 'requiere_archivo' => true]); // ID: 13 (Nuevo)


        // Criterios de Evaluación Maestro
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Legibilidad del Documento', 'descripcion_criterio' => 'El documento es claro y se puede leer sin dificultad.']); // ID: 1
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Vigencia del Documento', 'descripcion_criterio' => 'La fecha de vencimiento del documento es posterior a la fecha actual.']); // ID: 2
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Datos Coinciden', 'descripcion_criterio' => 'La información del documento coincide con los datos del trabajador/empresa/vehículo.']); // ID: 3
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Firma y Timbre', 'descripcion_criterio' => 'El documento cuenta con las firmas y/o timbres requeridos.']); // ID: 4
        CriterioEvaluacionMaestro::create(['nombre_criterio' => 'Completo y Sin Enmiendas', 'descripcion_criterio' => 'El documento está completo y no presenta alteraciones.']); // ID: 5

        // Condiciones de Contratista Maestro
        CondicionContratistaMaestro::create(['nombre_condicion' => 'Certificación ISO 9001', 'descripcion_condicion' => 'La empresa posee certificación ISO 9001 vigente.']); // ID: 1
        CondicionContratistaMaestro::create(['nombre_condicion' => 'Inscrita en ChileProveedores', 'descripcion_condicion' => 'La empresa está inscrita y habilitada en ChileProveedores.']); // ID: 2
        CondicionContratistaMaestro::create(['nombre_condicion' => 'Plan de Emergencia Aprobado', 'descripcion_condicion' => 'La empresa cuenta con un plan de emergencia aprobado.']); // ID: 3
        CondicionContratistaMaestro::create(['nombre_condicion' => 'No registra deudas previsionales', 'descripcion_condicion' => 'La empresa no registra deudas previsionales.']); // ID: 4

        // Condiciones de Trabajador Maestro
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Requiere Trabajar en Altura', 'descripcion_condicion' => 'Indica que el trabajador realizará labores sobre 1.8 metros.']); // ID: 1
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Opera Maquinaria Pesada Designada', 'descripcion_condicion' => 'Habilitado para operar maquinaria pesada específica según designación.']); // ID: 2
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Maneja Sustancias Químicas Peligrosas', 'descripcion_condicion' => 'Capacitado para el manejo seguro de sustancias químicas peligrosas.']); // ID: 3
        CondicionTrabajadorMaestro::create(['nombre_condicion' => 'Asignado a Faena Minera Subterránea', 'descripcion_condicion' => 'Condición para labores en mina subterránea.']); // ID: 4


        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Catálogos universales poblados exitosamente.');
    }
}
Use code with caution.
PHP
(He añadido un TipoDocumento "Certificado Curso de Altura" y he puesto los IDs de los primeros registros como comentario para referencia)
5. database/seeders/EmpresaMandanteSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaMandante;
use Illuminate\Support\Facades\DB;

class EmpresaMandanteSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        // Truncar tablas que dependen de EmpresaMandante antes de truncar EmpresaMandante
        // Orden de truncado puede ser importante.
        DB::table('configuracion_mandante_criterios')->truncate();
        DB::table('configuraciones_documentos_mandante')->truncate();
        DB::table('cargos')->truncate(); // Los cargos dependen de empresa_mandante_id
        DB::table('vinculaciones')->truncate(); // Las vinculaciones dependen de empresa_mandante_id

        EmpresaMandante::truncate();

        EmpresaMandante::create([ // ID: 1
            'rut_empresa_mandante' => '76000001-K',
            'nombre_empresa_mandante' => 'Gran Minera del Norte S.A.',
            'razon_social_mandante' => 'Gran Minera del Norte Sociedad Anónima',
            'direccion_mandante' => 'Av. Principal 123, Antofagasta',
            'ciudad_mandante' => 'Antofagasta',
            'telefono_mandante' => '+56552123456',
            'email_mandante' => 'contacto@granmineranorte.cl',
            'nombre_contacto_mandante' => 'Juan Pérez (Gerente SSO)',
            'email_contacto_mandante' => 'jperez.sso@granmineranorte.cl',
            'telefono_contacto_mandante' => '+56987654321',
            'activa' => true,
        ]);

        EmpresaMandante::create([ // ID: 2
            'rut_empresa_mandante' => '77000002-8',
            'nombre_empresa_mandante' => 'Constructora Austral Ltda.',
            'razon_social_mandante' => 'Constructora Austral y Compañía Limitada',
            'direccion_mandante' => 'Calle Sur 456, Santiago',
            'ciudad_mandante' => 'Santiago',
            'telefono_mandante' => '+56229876543',
            'email_mandante' => 'info@constructoraaustral.cl',
            'nombre_contacto_mandante' => 'Ana López (Jefa de Proyectos)',
            'email_contacto_mandante' => 'alopez.proyectos@constructoraaustral.cl',
            'telefono_contacto_mandante' => '+56912345678',
            'activa' => true,
        ]);

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Empresas mandantes de ejemplo creadas.');
    }
}
Use code with caution.
PHP
6. database/seeders/CargoVinculacionSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;
use App\Models\Vinculacion;
use App\Models\EmpresaMandante;
use App\Models\ConfiguracionDocumentoMandante;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CargoVinculacionSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        ConfiguracionDocumentoMandante::whereNotNull('cargo_id')->update(['cargo_id' => null]);
        ConfiguracionDocumentoMandante::whereNotNull('vinculacion_id')->update(['vinculacion_id' => null]);

        Cargo::truncate();
        Vinculacion::truncate();

        $mandanteMinera = EmpresaMandante::where('rut_empresa_mandante', '76000001-K')->first();
        $mandanteConstructora = EmpresaMandante::where('rut_empresa_mandante', '77000002-8')->first();

        if (!$mandanteMinera || !$mandanteConstructora) {
            $this->command->error('CargoVinculacionSeeder: No se encontraron las empresas mandantes de ejemplo. Asegúrate de ejecutar EmpresaMandanteSeeder primero.');
            Log::error('CargoVinculacionSeeder: No se encontraron las empresas mandantes de ejemplo.');
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            return;
        }

        // --- CARGOS ---
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Operador Camión Extracción', 'descripcion_cargo' => 'Conductor de camiones de alto tonelaje en faena minera.']); // ID: 1 (asumiendo)
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Soldador Calificado 6G', 'descripcion_cargo' => 'Soldador con certificación 6G para estructuras críticas.']);
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Supervisor Eléctrico Mina', 'descripcion_cargo' => 'Supervisor de trabajos eléctricos en mina.']);
        Cargo::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_cargo' => 'Geólogo de Exploración', 'descripcion_cargo' => 'Responsable de la exploración geológica en la mina.']);

        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Jefe de Obra', 'descripcion_cargo' => 'Responsable máximo de la ejecución de la obra.']);
        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Albañil', 'descripcion_cargo' => 'Trabajador de la construcción especializado en albañilería.']);
        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Prevencionista de Riesgos en Obra', 'descripcion_cargo' => 'Encargado de la seguridad y prevención en la obra.']);
        Cargo::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_cargo' => 'Topógrafo', 'descripcion_cargo' => 'Realiza mediciones y levantamientos topográficos.']);

        // --- VINCULACIONES ---
        $vMineraGerOper = Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Gerencia Operaciones Mina', 'descripcion_vinculacion' => 'Gerencia principal de operaciones en la mina.']);
        if($vMineraGerOper) { 
            Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Superintendencia Mantenimiento Mina', 'descripcion_vinculacion' => 'Área de mantenimiento dentro de operaciones.', 'parent_id' => $vMineraGerOper->id]);
            Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Departamento Chancado Primario', 'descripcion_vinculacion' => 'Departamento específico de chancado.', 'parent_id' => $vMineraGerOper->id]);
        }
        Vinculacion::create(['empresa_mandante_id' => $mandanteMinera->id, 'nombre_vinculacion' => 'Gerencia SSO Mina', 'descripcion_vinculacion' => 'Gerencia de Seguridad y Salud Ocupacional.']);

        $vConstructoraProy = Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Dirección de Proyectos Construcción', 'descripcion_vinculacion' => 'Dirección general de los proyectos.']);
        if($vConstructoraProy) {
            Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Proyecto Edificio Central', 'descripcion_vinculacion' => 'Vinculado al proyecto específico del Edificio Central.', 'parent_id' => $vConstructoraProy->id]);
            Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Obras Viales Ruta 5', 'descripcion_vinculacion' => 'Vinculado a las obras en la Ruta 5.', 'parent_id' => $vConstructoraProy->id]);
        }
        Vinculacion::create(['empresa_mandante_id' => $mandanteConstructora->id, 'nombre_vinculacion' => 'Departamento de Adquisiciones', 'descripcion_vinculacion' => 'Responsable de compras y adquisiciones.']);

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Cargos y Vinculaciones de ejemplo creados (todos asignados a mandantes específicos).');
    }
}
Use code with caution.
PHP
7. database/seeders/EmpresaContratistaSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpresaContratista;
use App\Models\CondicionContratistaMaestro;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Para la contraseña del usuario
use Spatie\Permission\Models\Role;   // Para asignar rol

class EmpresaContratistaSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        // Truncar tablas dependientes o desasociar
        DB::table('trabajadores')->truncate(); // Asumiendo que los trabajadores se crean aquí o después
        DB::table('vehiculos')->truncate();    // Asumiendo que los vehículos se crean aquí o después
        DB::table('documentos_adjuntos')->where('documentable_type', 'App\\Models\\EmpresaContratista')->delete(); // Polimórfica

        DB::table('empresa_contratista_condicion')->truncate();
        EmpresaContratista::truncate();
        // Considerar si los usuarios creados aquí deben eliminarse si no existen de antes.
        // User::whereIn('email', ['contratista.alpha@example.com', 'contratista.beta@example.com'])->delete();


        $roleContratistaAdmin = Role::where('name', 'ContratistaAdmin')->first();
        if (!$roleContratistaAdmin) {
            $this->command->error('EmpresaContratistaSeeder: Rol ContratistaAdmin no encontrado. Ejecuta RoleAndPermissionSeeder primero.');
            return;
        }

        $condicionIso = CondicionContratistaMaestro::where('nombre_condicion', 'Certificación ISO 9001')->first();
        $condicionChileProveedores = CondicionContratistaMaestro::where('nombre_condicion', 'Inscrita en ChileProveedores')->first();
        $condicionSinDeudas = CondicionContratistaMaestro::where('nombre_condicion', 'No registra deudas previsionales')->first();

        // Contratista 1
        $userAlpha = User::firstOrCreate(
            ['email' => 'contratista.alpha@example.com'],
            [
                'name' => 'Admin Contratista Alpha',
                'password' => Hash::make('password'),
            ]
        );
        $userAlpha->assignRole($roleContratistaAdmin);

        $contratista1 = EmpresaContratista::create([
            'rut_empresa_contratista' => '78000001-5',
            'nombre_empresa_contratista' => 'Servicios Industriales Alpha',
            'razon_social_contratista' => 'Servicios Industriales Alpha S.A.',
            'direccion_contratista' => 'Calle Falsa 123, Calama',
            'ciudad_contratista' => 'Calama',
            'email_contratista' => 'contacto@alpha-servicios.cl',
            'user_id' => $userAlpha->id,
            'activa' => true,
        ]);
        if ($contratista1 && $condicionIso) {
            $contratista1->condicionesContratistaMaestro()->attach($condicionIso->id);
        }
        if ($contratista1 && $condicionSinDeudas) {
            $contratista1->condicionesContratistaMaestro()->attach($condicionSinDeudas->id);
        }

        // Contratista 2
        $userBeta = User::firstOrCreate(
            ['email' => 'contratista.beta@example.com'],
            [
                'name' => 'Admin Contratista Beta',
                'password' => Hash::make('password'),
            ]
        );
        $userBeta->assignRole($roleContratistaAdmin);

        $contratista2 = EmpresaContratista::create([
            'rut_empresa_contratista' => '79000002-3',
            'nombre_empresa_contratista' => 'Transportes Beta Ltda.',
            'razon_social_contratista' => 'Transportes Beta y Cia Limitada',
            'direccion_contratista' => 'Av. Logistica 789, Santiago',
            'ciudad_contratista' => 'Santiago',
            'email_contratista' => 'info@transportesbeta.cl',
            'user_id' => $userBeta->id,
            'activa' => true,
        ]);
        if ($contratista2 && $condicionChileProveedores) {
            $contratista2->condicionesContratistaMaestro()->attach($condicionChileProveedores->id);
        }
         if ($contratista2 && $condicionSinDeudas) {
            $contratista2->condicionesContratistaMaestro()->attach($condicionSinDeudas->id);
        }


        // Contratista 3 (sin usuario predefinido aquí, se podría crear uno o dejar user_id null si se permite)
        // Para consistencia, también crearemos un usuario para este.
        $userGamma = User::firstOrCreate(
            ['email' => 'contratista.gamma@example.com'],
            [
                'name' => 'Admin Contratista Gamma (Inactiva)',
                'password' => Hash::make('password'),
            ]
        );
        $userGamma->assignRole($roleContratistaAdmin);

        EmpresaContratista::create([
            'rut_empresa_contratista' => '80000003-1',
            'nombre_empresa_contratista' => 'Consultores Gamma SPA',
            'razon_social_contratista' => 'Consultores Gamma Por Acciones',
            'direccion_contratista' => 'Oficina 101, Providencia',
            'ciudad_contratista' => 'Santiago',
            'email_contratista' => 'proyectos@consultoresgamma.com',
            'user_id' => $userGamma->id,
            'activa' => false, // Empresa inactiva
        ]);

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Empresas contratistas de ejemplo creadas, con usuarios y condiciones asignadas.');
    }
}
Use code with caution.
PHP
8. database/seeders/TrabajadorSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trabajador;
use App\Models\EmpresaContratista;
use App\Models\CondicionTrabajadorMaestro;
use Illuminate\Support\Facades\DB;

class TrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        DB::table('trabajador_condicion')->truncate();
        DB::table('documentos_adjuntos')->where('documentable_type', 'App\\Models\\Trabajador')->delete();
        Trabajador::truncate();

        $contratistaAlpha = EmpresaContratista::where('rut_empresa_contratista', '78000001-5')->first();
        $contratistaBeta = EmpresaContratista::where('rut_empresa_contratista', '79000002-3')->first();

        $condTrabAltura = CondicionTrabajadorMaestro::where('nombre_condicion', 'Requiere Trabajar en Altura')->first();
        $condOpMaqPesada = CondicionTrabajadorMaestro::where('nombre_condicion', 'Opera Maquinaria Pesada Designada')->first();

        if ($contratistaAlpha) {
            $trabajador1 = Trabajador::create([
                'empresa_contratista_id' => $contratistaAlpha->id,
                'rut_trabajador' => '15000001-1',
                'nombres_trabajador' => 'Carlos',
                'apellido_paterno_trabajador' => 'Soto',
                'apellido_materno_trabajador' => 'Rojas',
                'fecha_nacimiento_trabajador' => '1985-03-15',
                'nacionalidad_trabajador' => 'Chilena',
                'telefono_trabajador' => '911111111',
                'email_trabajador' => 'csoto@alpha.cl',
                'activo' => true,
            ]);
            if ($trabajador1 && $condTrabAltura) {
                $trabajador1->condicionesTrabajadorMaestro()->attach($condTrabAltura->id, [
                    'fecha_asignacion_condicion' => now()->subMonths(2),
                    'fecha_vencimiento_condicion' => now()->addYears(1), 
                    'valor_extra_condicion' => 'Proyecto Faena Principal'
                ]);
            }
             if ($trabajador1 && $condOpMaqPesada) {
                $trabajador1->condicionesTrabajadorMaestro()->attach($condOpMaqPesada->id, [
                    'fecha_asignacion_condicion' => now()->subMonths(6),
                    'valor_extra_condicion' => 'Excavadora CAT 320'
                ]);
            }

            Trabajador::create([
                'empresa_contratista_id' => $contratistaAlpha->id,
                'rut_trabajador' => '16000002-2',
                'nombres_trabajador' => 'Luisa',
                'apellido_paterno_trabajador' => 'Méndez',
                'apellido_materno_trabajador' => 'Tapia',
                'fecha_nacimiento_trabajador' => '1990-07-20',
                'nacionalidad_trabajador' => 'Peruana',
                'activo' => true,
            ]);
        }

        if ($contratistaBeta) {
            Trabajador::create([
                'empresa_contratista_id' => $contratistaBeta->id,
                'rut_trabajador' => '17000003-3',
                'nombres_trabajador' => 'Pedro',
                'apellido_paterno_trabajador' => 'González',
                'apellido_materno_trabajador' => 'Pérez',
                'fecha_nacimiento_trabajador' => '1988-11-05',
                'nacionalidad_trabajador' => 'Chilena',
                'activo' => true,
            ]);
            Trabajador::create([
                'empresa_contratista_id' => $contratistaBeta->id,
                'rut_trabajador' => '18000004-4',
                'nombres_trabajador' => 'Ana',
                'apellido_paterno_trabajador' => 'Silva',
                'apellido_materno_trabajador' => 'Castro',
                'fecha_nacimiento_trabajador' => '1992-01-30',
                'nacionalidad_trabajador' => 'Argentina',
                'activo' => false, // Trabajador inactivo
            ]);
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Trabajadores de ejemplo creados y condiciones asignadas.');
    }
}
Use code with caution.
PHP
9. database/seeders/ConfiguracionDocumentoMandanteSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionDocumentoMandante;
use App\Models\EmpresaMandante;
use App\Models\TipoDocumento;
use App\Models\Cargo;
use App\Models\Vinculacion; // Asegúrate que esté importado
use App\Models\CondicionContratistaMaestro;
use App\Models\CondicionTrabajadorMaestro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfiguracionDocumentoMandanteSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        // Truncar primero la tabla pivote de criterios si existe y depende de esta
        DB::table('configuracion_mandante_criterios')->truncate();
        ConfiguracionDocumentoMandante::truncate();

        // Obtener entidades necesarias
        $mandanteMinera = EmpresaMandante::where('rut_empresa_mandante', '76000001-K')->first(); // ID 1
        $mandanteConstructora = EmpresaMandante::where('rut_empresa_mandante', '77000002-8')->first(); // ID 2

        $docCedula = TipoDocumento::where('nombre', 'Cédula de Identidad')->first(); // ID 1
        $docLicencia = TipoDocumento::where('nombre', 'Licencia de Conducir')->first(); // ID 2
        $docContrato = TipoDocumento::where('nombre', 'Contrato de Trabajo')->first(); // ID 4
        $docExamenAltura = TipoDocumento::where('nombre', 'Examen de Altura Física')->first(); // ID 7
        $docPermisoCirculacion = TipoDocumento::where('nombre', 'Permiso de Circulación')->first(); // ID 8
        $docEscrituraEmpresa = TipoDocumento::where('nombre', 'Escritura de Constitución Empresa')->first(); // ID 11
        $docCursoAltura = TipoDocumento::where('nombre', 'Certificado Curso de Altura')->first(); // ID 13 (nuevo)

        // Es crucial que los cargos/vinculaciones se obtengan filtrando por el mandante correcto
        $cargoOpCamionMinera = Cargo::where('nombre_cargo', 'Operador Camión Extracción')
                                ->where('empresa_mandante_id', $mandanteMinera ? $mandanteMinera->id : null)
                                ->first();
        $cargoJefeObraConstructora = Cargo::where('nombre_cargo', 'Jefe de Obra')
                                     ->where('empresa_mandante_id', $mandanteConstructora ? $mandanteConstructora->id : null)
                                     ->first();
        
        // Ejemplo de vinculación (asegúrate que esta vinculación exista para el mandanteMinera)
        $vincGerOperMinera = Vinculacion::where('nombre_vinculacion', 'Gerencia Operaciones Mina')
                                  ->where('empresa_mandante_id', $mandanteMinera ? $mandanteMinera->id : null)
                                  ->first();


        $condContratistaISO = CondicionContratistaMaestro::where('nombre_condicion', 'Certificación ISO 9001')->first(); // ID 1
        $condTrabAltura = CondicionTrabajadorMaestro::where('nombre_condicion', 'Requiere Trabajar en Altura')->first(); // ID 1


        if (!$mandanteMinera || !$mandanteConstructora) {
            $this->command->error('ConfiguracionDocumentoMandanteSeeder: Mandantes no encontrados.');
            Log::error('ConfiguracionDocumentoMandanteSeeder: Mandantes no encontrados.');
            return;
        }
        if (!$docCedula || !$docLicencia || !$docContrato || !$docExamenAltura || !$docPermisoCirculacion || !$docEscrituraEmpresa || !$docCursoAltura) {
            $this->command->error('ConfiguracionDocumentoMandanteSeeder: Tipos de documentos base no encontrados.');
            Log::error('ConfiguracionDocumentoMandanteSeeder: Tipos de documentos base no encontrados.');
            return;
        }


        // Configuraciones para Mandante Minera (ID 1)
        if ($mandanteMinera) {
            // Cédula para todos los trabajadores
            if ($docCedula) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteMinera->id, 'tipo_documento_id' => $docCedula->id,
                    'entidad_controlada' => 'TRABAJADOR', 'es_obligatorio' => true,
                    'observaciones' => 'Cédula de Identidad para todos los trabajadores en Minera.',
                    // Para TRABAJADOR, al menos uno de (cargo, vinculacion, cond_trabajador) o ninguno si es general para el trabajador.
                    // Si es general, la lógica de DocumentRequirementService lo interpretará.
                    // Aquí asumimos que si no se especifica cargo/vinc/cond, aplica a *todos* los trabajadores.
                ]);
            }
            // Contrato para todos los trabajadores
             if ($docContrato) {
                 ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteMinera->id, 'tipo_documento_id' => $docContrato->id,
                    'entidad_controlada' => 'TRABAJADOR', 'es_obligatorio' => true,
                ]);
            }
            // Licencia para Operador Camión Extracción
            if ($docLicencia && $cargoOpCamionMinera) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteMinera->id, 'tipo_documento_id' => $docLicencia->id,
                    'entidad_controlada' => 'TRABAJADOR', 'cargo_id' => $cargoOpCamionMinera->id, 'es_obligatorio' => true,
                ]);
            }
            // Curso de Altura para trabajadores con Condición "Requiere Trabajar en Altura"
            if ($docCursoAltura && $condTrabAltura) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteMinera->id, 'tipo_documento_id' => $docCursoAltura->id,
                    'entidad_controlada' => 'TRABAJADOR', 'condicion_trabajador_id' => $condTrabAltura->id,
                    'es_obligatorio' => true, 'observaciones' => 'Certificado curso de altura si el trabajador tiene la condición.',
                ]);
            }
            // Examen de Altura Física para trabajadores con Condición "Requiere Trabajar en Altura"
            if ($docExamenAltura && $condTrabAltura) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteMinera->id, 'tipo_documento_id' => $docExamenAltura->id,
                    'entidad_controlada' => 'TRABAJADOR', 'condicion_trabajador_id' => $condTrabAltura->id,
                    'es_obligatorio' => true, 'observaciones' => 'Examen médico si tiene la condición de trabajo en altura.',
                ]);
            }
            // Permiso de Circulación para todos los Vehículos
            if ($docPermisoCirculacion) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteMinera->id, 'tipo_documento_id' => $docPermisoCirculacion->id,
                    'entidad_controlada' => 'VEHICULO', 'es_obligatorio' => true,
                ]);
            }
            // Escritura de Empresa para contratistas con Condición "Certificación ISO 9001"
            if ($docEscrituraEmpresa && $condContratistaISO) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteMinera->id, 'tipo_documento_id' => $docEscrituraEmpresa->id,
                    'entidad_controlada' => 'EMPRESA', 'condicion_contratista_id' => $condContratistaISO->id,
                    'es_obligatorio' => true, 'observaciones' => 'Solo si la contratista declara tener ISO 9001.',
                ]);
            }
        }

        // Configuraciones para Mandante Constructora (ID 2)
        if ($mandanteConstructora) {
            // Cédula para todos los trabajadores
            if ($docCedula) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteConstructora->id, 'tipo_documento_id' => $docCedula->id,
                    'entidad_controlada' => 'TRABAJADOR', 'es_obligatorio' => true,
                ]);
            }
            // Contrato para Jefes de Obra
            if ($docContrato && $cargoJefeObraConstructora) {
                 ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteConstructora->id, 'tipo_documento_id' => $docContrato->id,
                    'entidad_controlada' => 'TRABAJADOR', 'cargo_id' => $cargoJefeObraConstructora->id, 'es_obligatorio' => true,
                ]);
            }
            // Escritura de Empresa para todas las Empresas Contratistas
            if ($docEscrituraEmpresa) {
                ConfiguracionDocumentoMandante::create([
                    'empresa_mandante_id' => $mandanteConstructora->id, 'tipo_documento_id' => $docEscrituraEmpresa->id,
                    'entidad_controlada' => 'EMPRESA', 'es_obligatorio' => true,
                ]);
            }
        }

        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Configuraciones de documentos mandante de ejemplo creadas.');
    }
}
Use code with caution.
PHP
10. database/seeders/ConfiguracionMandanteCriterioSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionDocumentoMandante; // No necesita ConfiguracionMandanteCriterio aquí directamente
use App\Models\CriterioEvaluacionMaestro;
use App\Models\EmpresaMandante;
use App\Models\TipoDocumento;
use App\Models\Cargo; // Para buscar el cargo correcto
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfiguracionMandanteCriterioSeeder extends Seeder
{
    public function run(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        DB::table('configuracion_mandante_criterios')->truncate();

        $critLegible = CriterioEvaluacionMaestro::where('nombre_criterio', 'Legibilidad del Documento')->first(); // ID 1
        $critVigente = CriterioEvaluacionMaestro::where('nombre_criterio', 'Vigencia del Documento')->first(); // ID 2
        $critDatosCoinciden = CriterioEvaluacionMaestro::where('nombre_criterio', 'Datos Coinciden')->first(); // ID 3
        $critFirmaTimbre = CriterioEvaluacionMaestro::where('nombre_criterio', 'Firma y Timbre')->first(); // ID 4

        $mandanteMinera = EmpresaMandante::where('rut_empresa_mandante', '76000001-K')->first(); // ID 1
        $docCedula = TipoDocumento::where('nombre', 'Cédula de Identidad')->first(); // ID 1
        $docLicencia = TipoDocumento::where('nombre', 'Licencia de Conducir')->first(); // ID 2
        $docContrato = TipoDocumento::where('nombre', 'Contrato de Trabajo')->first(); // ID 4
        $cargoOpCamionMinera = Cargo::where('nombre_cargo', 'Operador Camión Extracción')
                                ->where('empresa_mandante_id', $mandanteMinera ? $mandanteMinera->id : null)
                                ->first(); // ID 1 (asumiendo)

        if (!$critLegible || !$critVigente || !$critDatosCoinciden || !$critFirmaTimbre || !$mandanteMinera || !$docCedula || !$docLicencia || !$docContrato) {
             $this->command->error('ConfiguracionMandanteCriterioSeeder: Faltan entidades base (criterios, mandante, tipos de doc).');
             Log::error('ConfiguracionMandanteCriterioSeeder: Faltan entidades base.');
             if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
             }
             return;
        }
        
        // --- Criterios para Cédula de Identidad (TRABAJADOR general) en Mandante Minera ---
        $configCedulaMinera = ConfiguracionDocumentoMandante::where('empresa_mandante_id', $mandanteMinera->id)
                                ->where('tipo_documento_id', $docCedula->id)
                                ->where('entidad_controlada', 'TRABAJADOR')
                                // Ser más específico si hay varias reglas para Cédula+Trabajador, ej. filtrando por cargo_id, etc. null
                                ->whereNull('cargo_id') 
                                ->whereNull('vinculacion_id')
                                ->whereNull('condicion_trabajador_id')
                                ->first();

        if ($configCedulaMinera) {
            $configCedulaMinera->criteriosEvaluacionMaestro()->attach($critLegible->id, ['es_criterio_obligatorio' => true]);
            $configCedulaMinera->criteriosEvaluacionMaestro()->attach($critVigente->id, ['es_criterio_obligatorio' => true, 'instruccion_adicional_criterio' => 'Revisar especialmente la fecha de vencimiento.']);
            $configCedulaMinera->criteriosEvaluacionMaestro()->attach($critDatosCoinciden->id, ['es_criterio_obligatorio' => true]);
        } else {
             Log::warning("ConfiguracionMandanteCriterioSeeder: No se encontró la configuración para Cédula General en Mandante Minera para asignar criterios.");
        }

        // --- Criterios para Licencia de Conducir (Operador Camión) en Mandante Minera ---
        if ($cargoOpCamionMinera) { // Asegurarse que el cargo exista
            $configLicenciaMineraOp = ConfiguracionDocumentoMandante::where('empresa_mandante_id', $mandanteMinera->id)
                                        ->where('tipo_documento_id', $docLicencia->id)
                                        ->where('cargo_id', $cargoOpCamionMinera->id) // Específico para el cargo
                                        ->where('entidad_controlada', 'TRABAJADOR')
                                        ->first();
            if ($configLicenciaMineraOp) {
                $configLicenciaMineraOp->criteriosEvaluacionMaestro()->attach($critLegible->id, ['es_criterio_obligatorio' => true]);
                $configLicenciaMineraOp->criteriosEvaluacionMaestro()->attach($critVigente->id, ['es_criterio_obligatorio' => true]);
                $configLicenciaMineraOp->criteriosEvaluacionMaestro()->attach($critDatosCoinciden->id, ['es_criterio_obligatorio' => true, 'instruccion_adicional_criterio' => 'Verificar que la clase de licencia sea la correcta para el cargo.']);
            } else {
                 Log::warning("ConfiguracionMandanteCriterioSeeder: No se encontró la configuración para Licencia de Operador Camión en Mandante Minera.");
            }
        } else {
            Log::warning("ConfiguracionMandanteCriterioSeeder: No se encontró el Cargo 'Operador Camión Extracción' para Mandante Minera.");
        }


        // --- Criterios para Contrato de Trabajo (TRABAJADOR general) en Mandante Minera ---
        $configContratoMinera = ConfiguracionDocumentoMandante::where('empresa_mandante_id', $mandanteMinera->id)
                                ->where('tipo_documento_id', $docContrato->id)
                                ->where('entidad_controlada', 'TRABAJADOR')
                                ->whereNull('cargo_id')
                                ->whereNull('vinculacion_id')
                                ->whereNull('condicion_trabajador_id')
                                ->first();
        if ($configContratoMinera) {
            $configContratoMinera->criteriosEvaluacionMaestro()->attach($critLegible->id); // Por defecto no obligatorio si no se especifica
            $configContratoMinera->criteriosEvaluacionMaestro()->attach($critFirmaTimbre->id, ['es_criterio_obligatorio' => true, 'instruccion_adicional_criterio' => 'Debe estar firmado por ambas partes.']);
            $configContratoMinera->criteriosEvaluacionMaestro()->attach($critDatosCoinciden->id, ['es_criterio_obligatorio' => true]);
        } else {
            Log::warning("ConfiguracionMandanteCriterioSeeder: No se encontró la configuración para Contrato General en Mandante Minera.");
        }


        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        $this->command->info('Criterios de evaluación asignados a configuraciones de ejemplo.');
    }
}
Use code with caution.
PHP
¡Y eso es todo! Con esto, tienes el volcado completo del código del proyecto ASEM hasta nuestro último punto de desarrollo.
Esto debería darte una base sólida para continuar o para revisar en un nuevo chat si es necesario. Si tienes alguna pregunta sobre algún archivo específico o quieres pasar al siguiente paso (completar el CRUD de EmpresasContratistas o el de ConfiguracionDocumentoMandante), házmelo saber.

