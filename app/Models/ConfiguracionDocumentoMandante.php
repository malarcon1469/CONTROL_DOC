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
        'entidad_controlada',
        'cargo_id',
        'vinculacion_id',
        'condicion_contratista_id',
        'condicion_trabajador_id',
        'es_obligatorio',
        'observaciones',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Empresa mandante a la que pertenece esta configuración.
     */
    public function empresaMandante()
    {
        return $this->belongsTo(EmpresaMandante::class, 'empresa_mandante_id');
    }

    /**
     * Tipo de documento que esta configuración requiere.
     */
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    /**
     * Cargo al que aplica esta configuración (si aplica).
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    /**
     * Vinculación a la que aplica esta configuración (si aplica).
     */
    public function vinculacion()
    {
        return $this->belongsTo(Vinculacion::class, 'vinculacion_id');
    }

    /**
     * Condición de contratista que activa esta configuración (si aplica).
     */
    public function condicionContratistaMaestro()
    {
        return $this->belongsTo(CondicionContratistaMaestro::class, 'condicion_contratista_id');
    }

    /**
     * Condición de trabajador que activa esta configuración (si aplica).
     */
    public function condicionTrabajadorMaestro()
    {
        return $this->belongsTo(CondicionTrabajadorMaestro::class, 'condicion_trabajador_id');
    }

    /**
     * Criterios de evaluación específicos para esta configuración de documento.
     */
    public function criteriosEvaluacionMaestro()
    {
        // El modelo CriterioEvaluacionMaestro ya fue definido.
        // El modelo ConfiguracionMandanteCriterio (Pivot) aún no se ha creado.
        return $this->belongsToMany(
            CriterioEvaluacionMaestro::class,
            'configuracion_mandante_criterios', // Nombre de la tabla pivote
            'configuracion_documento_id',
            'criterio_evaluacion_id'
        )
        ->using(ConfiguracionMandanteCriterio::class) // Modelo Pivot personalizado
        ->withPivot(['es_criterio_obligatorio', 'instruccion_adicional_criterio'])
        ->withTimestamps();
    }

    /**
     * Documentos adjuntos que cumplen con esta configuración específica.
     */
    public function documentosAdjuntos()
    {
        // El modelo DocumentoAdjunto aún no se ha creado.
        return $this->hasMany(DocumentoAdjunto::class, 'configuracion_documento_mandante_id');
    }
}
