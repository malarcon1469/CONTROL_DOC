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

    // --- RELACIONES ---

    /**
     * Empresa contratista a la que pertenece este trabajador.
     */
    public function empresaContratista()
    {
        return $this->belongsTo(EmpresaContratista::class, 'empresa_contratista_id');
    }

    /**
     * Condiciones (del maestro) que este trabajador cumple.
     * Se utiliza un modelo Pivot personalizado (TrabajadorCondicion) para acceder a campos extra de la tabla pivote.
     */
    public function condicionesTrabajadorMaestro()
    {
        return $this->belongsToMany(
            CondicionTrabajadorMaestro::class,
            'trabajador_condicion', // Nombre de la tabla pivote
            'trabajador_id',
            'condicion_trabajador_id'
        )
        ->using(TrabajadorCondicion::class) // Modelo Pivot personalizado
        ->withPivot(['fecha_asignacion_condicion', 'fecha_vencimiento_condicion', 'valor_extra_condicion'])
        ->withTimestamps(); // Si la tabla pivote tiene timestamps (created_at, updated_at)
    }

    /**
     * Documentos adjuntos pertenecientes a este trabajador (usando relación polimórfica).
     */
    public function documentosAdjuntos()
    {
        // El modelo DocumentoAdjunto aún no se ha creado formalmente en este paso,
        // pero se define la relación para cuando exista.
        return $this->morphMany(DocumentoAdjunto::class, 'documentable');
    }
}