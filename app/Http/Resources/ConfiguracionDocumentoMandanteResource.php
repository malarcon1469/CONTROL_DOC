<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfiguracionDocumentoMandanteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
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

            // Relaciones cargadas
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
                return [
                    'id' => $this->cargo->id,
                    'nombre_cargo' => $this->cargo->nombre_cargo,
                ];
            }),
            'vinculacion' => $this->whenLoaded('vinculacion', function () {
                return [
                    'id' => $this->vinculacion->id,
                    'nombre_vinculacion' => $this->vinculacion->nombre_vinculacion,
                ];
            }),
            'condicion_contratista' => $this->whenLoaded('condicionContratistaMaestro', function () {
                return [
                    'id' => $this->condicionContratistaMaestro->id,
                    'nombre_condicion' => $this->condicionContratistaMaestro->nombre_condicion,
                ];
            }),
            'condicion_trabajador' => $this->whenLoaded('condicionTrabajadorMaestro', function () {
                return [
                    'id' => $this->condicionTrabajadorMaestro->id,
                    'nombre_condicion' => $this->condicionTrabajadorMaestro->nombre_condicion,
                ];
            }),

            // Criterios de Evaluación con datos de la tabla pivote
            'criterios_evaluacion' => $this->whenLoaded('criteriosEvaluacionMaestro', function () {
                return $this->criteriosEvaluacionMaestro->map(function ($criterio) {
                    return [
                        'id_criterio_maestro' => $criterio->id,
                        'nombre_criterio' => $criterio->nombre_criterio,
                        'descripcion_criterio' => $criterio->descripcion_criterio,
                        // Datos de la tabla pivote (ConfiguracionMandanteCriterio)
                        'pivot_id_configuracion_criterio' => $criterio->pivot->id, // ID del registro en la tabla pivote
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