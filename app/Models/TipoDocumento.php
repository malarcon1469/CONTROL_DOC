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
        'deleted_at' => 'datetime', // Necesario para SoftDeletes si se consulta directamente
    ];

    // --- RELACIONES ---

    /**
     * Configuraciones de documentos que usan este tipo de documento.
     */
    public function configuracionesDocumentosMandante()
    {
        // El modelo ConfiguracionDocumentoMandante aún no se ha creado,
        // pero definimos la relación para cuando exista.
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'tipo_documento_id');
    }

    /**
     * Documentos adjuntos de este tipo.
     */
    public function documentosAdjuntos()
    {
        // El modelo DocumentoAdjunto aún no se ha creado.
        return $this->hasMany(DocumentoAdjunto::class, 'tipo_documento_id');
    }
}