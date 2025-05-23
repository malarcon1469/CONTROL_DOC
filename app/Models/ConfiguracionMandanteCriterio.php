<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ConfiguracionMandanteCriterio extends Pivot
{
    protected $table = 'configuracion_mandante_criterios';

    public $incrementing = true; // La tabla pivote tiene su propio 'id' autoincremental
    public $timestamps = true; // La tabla pivote tiene timestamps

    protected $fillable = [
        'configuracion_documento_id',
        'criterio_evaluacion_id',
        'es_criterio_obligatorio',
        'instruccion_adicional_criterio',
    ];

    protected $casts = [
        'es_criterio_obligatorio' => 'boolean',
    ];

    // Relaciones inversas (opcional, pero puede ser útil)
    public function configuracionDocumentoMandante()
    {
        return $this->belongsTo(ConfiguracionDocumentoMandante::class, 'configuracion_documento_id');
    }

    public function criterioEvaluacionMaestro()
    {
        return $this->belongsTo(CriterioEvaluacionMaestro::class, 'criterio_evaluacion_id');
    }
}