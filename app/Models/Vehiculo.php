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
        'tipo_vehiculo',
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

    // --- RELACIONES ---

    /**
     * Empresa contratista a la que pertenece este vehículo.
     */
    public function empresaContratista()
    {
        return $this->belongsTo(EmpresaContratista::class, 'empresa_contratista_id');
    }

    /**
     * Documentos adjuntos pertenecientes a este vehículo (usando relación polimórfica).
     */
    public function documentosAdjuntos()
    {
        // El modelo DocumentoAdjunto ya fue definido.
        return $this->morphMany(DocumentoAdjunto::class, 'documentable');
    }
}