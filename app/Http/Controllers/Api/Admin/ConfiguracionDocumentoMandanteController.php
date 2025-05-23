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
use App\Http\Resources\ConfiguracionDocumentoMandanteResource; // Lo crearemos después
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

        // Base de reglas comunes
        $rules = [
            'empresa_mandante_id' => ['required', 'integer', Rule::exists('empresas_mandantes', 'id')->whereNull('deleted_at')],
            'tipo_documento_id' => ['required', 'integer', Rule::exists('tipos_documentos', 'id')->whereNull('deleted_at')],
            'entidad_controlada' => ['required', 'string', Rule::in(['EMPRESA', 'TRABAJADOR', 'VEHICULO'])],
            'es_obligatorio' => 'required|boolean',
            'observaciones' => 'nullable|string|max:2000',

            // Campos condicionales basados en entidad_controlada
            'cargo_id' => [
                Rule::requiredIf(fn() => $entidadControlada === 'TRABAJADOR' && !$request->input('vinculacion_id') && !$request->input('condicion_trabajador_id')),
                'nullable', 'integer',
                Rule::exists('cargos', 'id')->where(function ($query) use ($empresaMandanteId) {
                    $query->where('empresa_mandante_id', $empresaMandanteId)->orWhereNull('empresa_mandante_id'); // Cargos globales o del mandante
                })->whereNull('deleted_at'),
                // Asegurar que si se provee, entidad_controlada sea TRABAJADOR
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'TRABAJADOR') {
                        $fail('El campo cargo_id solo es aplicable si la entidad controlada es TRABAJADOR.');
                    }
                },
            ],
            'vinculacion_id' => [
                Rule::requiredIf(fn() => $entidadControlada === 'TRABAJADOR' && !$request->input('cargo_id') && !$request->input('condicion_trabajador_id')),
                'nullable', 'integer',
                Rule::exists('vinculaciones', 'id')->where(function ($query) use ($empresaMandanteId) {
                    $query->where('empresa_mandante_id', $empresaMandanteId)->orWhereNull('empresa_mandante_id'); // Vinculaciones globales o del mandante
                })->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'TRABAJADOR') {
                        $fail('El campo vinculacion_id solo es aplicable si la entidad controlada es TRABAJADOR.');
                    }
                },
            ],
            'condicion_contratista_id' => [
                'nullable', 'integer',
                Rule::exists('condiciones_contratista_maestro', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'EMPRESA') {
                        $fail('El campo condicion_contratista_id solo es aplicable si la entidad controlada es EMPRESA.');
                    }
                },
            ],
            'condicion_trabajador_id' => [
                Rule::requiredIf(fn() => $entidadControlada === 'TRABAJADOR' && !$request->input('cargo_id') && !$request->input('vinculacion_id')),
                'nullable', 'integer',
                Rule::exists('condiciones_trabajador_maestro', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($entidadControlada) {
                    if ($value && $entidadControlada !== 'TRABAJADOR') {
                        $fail('El campo condicion_trabajador_id solo es aplicable si la entidad controlada es TRABAJADOR.');
                    }
                },
            ],

            // Criterios asociados (array de objetos)
            'criterios' => 'nullable|array',
            'criterios.*.criterio_evaluacion_id' => ['required_with:criterios', 'integer', Rule::exists('criterios_evaluacion_maestro', 'id')->whereNull('deleted_at')],
            'criterios.*.es_criterio_obligatorio' => 'required_with:criterios|boolean',
            'criterios.*.instruccion_adicional_criterio' => 'nullable|string|max:1000',
        ];

        // Regla de unicidad compuesta para la configuración
        // Una configuración es única por la combinación de sus campos significativos.
        // Esta regla es compleja y puede ajustarse según la granularidad deseada.
        // Por ahora, validamos que no exista una regla idéntica para el mismo mandante, tipo doc, entidad y las mismas condiciones.
        $uniqueRule = Rule::unique('configuraciones_documentos_mandante')->where(function ($query) use ($request, $empresaMandanteId, $entidadControlada) {
            $query->where('empresa_mandante_id', $empresaMandanteId)
                  ->where('tipo_documento_id', $request->input('tipo_documento_id'))
                  ->where('entidad_controlada', $entidadControlada)
                  ->where('cargo_id', $request->input('cargo_id')) // null si no se provee
                  ->where('vinculacion_id', $request->input('vinculacion_id')) // null si no se provee
                  ->where('condicion_contratista_id', $request->input('condicion_contratista_id')) // null si no se provee
                  ->where('condicion_trabajador_id', $request->input('condicion_trabajador_id')); // null si no se provee
        })->withoutTrashed();

        if ($configuracionId) {
            $uniqueRule->ignore($configuracionId);
        }
        // Se podría aplicar esta regla al 'tipo_documento_id' o a un campo ficticio para que aparezca en el array 'errors'
        // Aquí la aplicaremos a 'tipo_documento_id' como ejemplo si otros campos también fallan
        $rules['tipo_documento_id'][] = $uniqueRule;


        // Validación adicional: Para TRABAJADOR, al menos uno de cargo, vinculación o condición_trabajador debe estar presente.
        if ($entidadControlada === 'TRABAJADOR') {
            $rules['cargo_id'][] = function ($attribute, $value, $fail) use ($request) {
                if (
                    empty($value) && // cargo_id está vacío
                    empty($request->input('vinculacion_id')) &&
                    empty($request->input('condicion_trabajador_id'))
                ) {
                    $fail('Para la entidad TRABAJADOR, debe especificar al menos un Cargo, Vinculación o Condición de Trabajador.');
                }
            };
        }


        // Limpiar campos no aplicables antes de la validación de unicidad o de guardar
        // Esto se manejará antes de $config->create() o $config->update()

        return $rules;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ConfiguracionDocumentoMandante::query()->with([
            'empresaMandante:id,nombre_empresa_mandante', // Seleccionar solo campos necesarios
            'tipoDocumento:id,nombre',
            'cargo:id,nombre_cargo',
            'vinculacion:id,nombre_vinculacion',
            'condicionContratistaMaestro:id,nombre_condicion',
            'condicionTrabajadorMaestro:id,nombre_condicion',
            'criteriosEvaluacionMaestro', // Carga los criterios a través de la tabla pivote
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

        $configuraciones = $query->get();

        return ConfiguracionDocumentoMandanteResource::collection($configuraciones)
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate($this->getRules($request));

        // Preparar datos para la creación, limpiando campos no aplicables según entidad_controlada
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

        // Si la entidad es VEHICULO, todos los campos condicionales deben ser null.
        if ($entidad === 'VEHICULO') {
            $dataToCreate['cargo_id'] = null;
            $dataToCreate['vinculacion_id'] = null;
            $dataToCreate['condicion_trabajador_id'] = null;
            $dataToCreate['condicion_contratista_id'] = null;
        }


        DB::beginTransaction();
        try {
            $configuracion = ConfiguracionDocumentoMandante::create($dataToCreate);

            // Sincronizar criterios si se proporcionaron
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

            // Cargar relaciones para la respuesta
            $configuracion->load([
                'empresaMandante:id,nombre_empresa_mandante',
                'tipoDocumento:id,nombre',
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
                'error' => $e->getMessage() // Solo en desarrollo
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        // Implementación pendiente
        $configuracion = ConfiguracionDocumentoMandante::with([
            'empresaMandante:id,nombre_empresa_mandante',
            'tipoDocumento:id,nombre',
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Implementación pendiente
        return response()->json(['message' => 'Funcionalidad de actualizar aún no implementada.'], Response::HTTP_NOT_IMPLEMENTED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        // Implementación pendiente
        return response()->json(['message' => 'Funcionalidad de eliminar aún no implementada.'], Response::HTTP_NOT_IMPLEMENTED);
    }
}