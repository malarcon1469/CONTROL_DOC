<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CondicionTrabajadorMaestro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'condiciones_trabajador_maestro';

    protected $fillable = [
        'nombre_condicion',
        'descripcion_condicion',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Trabajadores que tienen esta condición asignada.
     * Se utiliza un modelo Pivot personalizado (TrabajadorCondicion) para acceder a campos extra de la tabla pivote.
     */
    public function trabajadores()
    {
        return $this->belongsToMany(
            Trabajador::class,
            'trabajador_condicion', // Nombre de la tabla pivote
            'condicion_trabajador_id',
            'trabajador_id'
        )
        ->using(TrabajadorCondicion::class) // Modelo Pivot personalizado
        ->withPivot(['fecha_asignacion_condicion', 'fecha_vencimiento_condicion', 'valor_extra_condicion'])
        ->withTimestamps(); // Si la tabla pivote tiene timestamps (created_at, updated_at)
    }

    /**
     * Configuraciones de documentos mandante que dependen de esta condición de trabajador.
     */
    public function configuracionesDocumentosMandante()
    {
        // El modelo ConfiguracionDocumentoMandante aún no se ha creado formalmente en este paso,
        // pero se define la relación para cuando exista.
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'condicion_trabajador_id');
    }
}