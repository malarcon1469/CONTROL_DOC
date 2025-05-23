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
        'tipo_documento_id',
        'documentable_id',
        'documentable_type',
        'subido_por_user_id',
        'nombre_original_archivo',
        'path_archivo',
        'mime_type',
        'tamano_archivo',
        'estado_validacion',
        'observaciones_validacion',
        'fecha_emision_documento',
        'fecha_vencimiento_documento',
        'validado_por_user_id',
        'fecha_validacion',
        'configuracion_documento_mandante_id',
    ];

    protected $casts = [
        'fecha_emision_documento' => 'date',
        'fecha_vencimiento_documento' => 'date',
        'fecha_validacion' => 'datetime',
        'tamano_archivo' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * El tipo de documento al que pertenece este adjunto.
     */
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    /**
     * Relación polimórfica: Obtiene el modelo padre al que pertenece el documento
     * (puede ser Trabajador, EmpresaContratista, Vehiculo).
     */
    public function documentable()
    {
        return $this->morphTo();
    }

    /**
     * Usuario que subió el documento.
     */
    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por_user_id');
    }

    /**
     * Usuario (Analista ASEM) que validó el documento.
     */
    public function validadoPor()
    {
        return $this->belongsTo(User::class, 'validado_por_user_id');
    }

    /**
     * La configuración de documento específica que este adjunto intenta cumplir (opcional).
     */
    public function configuracionDocumentoMandante()
    {
        return $this->belongsTo(ConfiguracionDocumentoMandante::class, 'configuracion_documento_mandante_id');
    }
}