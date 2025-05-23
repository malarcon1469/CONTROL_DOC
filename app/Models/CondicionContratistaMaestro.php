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

    // --- RELACIONES ---

    /**
     * Empresas contratistas que tienen esta condición asignada.
     */
    public function empresasContratistas()
    {
        // El modelo EmpresaContratista aún no se ha creado.
        return $this->belongsToMany(
            EmpresaContratista::class,
            'empresa_contratista_condicion',
            'condicion_contratista_id',
            'empresa_contratista_id'
        )->withTimestamps(); // La tabla pivote empresa_contratista_condicion tiene timestamps.
    }

    /**
     * Configuraciones de documentos mandante que dependen de esta condición de contratista.
     */
    public function configuracionesDocumentosMandante()
    {
        // El modelo ConfiguracionDocumentoMandante aún no se ha creado.
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'condicion_contratista_id');
    }
}