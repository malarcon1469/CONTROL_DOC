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

    // --- RELACIONES ---

    /**
     * Configuraciones de documentos mandante que utilizan este criterio de evaluación.
     * Se utiliza un modelo Pivot personalizado (ConfiguracionMandanteCriterio).
     */
    public function configuracionesDocumentosMandante()
    {
        return $this->belongsToMany(
            ConfiguracionDocumentoMandante::class,
            'configuracion_mandante_criterios', // Nombre de la tabla pivote
            'criterio_evaluacion_id',
            'configuracion_documento_id'
        )
        ->using(ConfiguracionMandanteCriterio::class) // Modelo Pivot personalizado
        ->withPivot(['es_criterio_obligatorio', 'instruccion_adicional_criterio'])
        ->withTimestamps();
    }
}