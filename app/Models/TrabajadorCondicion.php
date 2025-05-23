<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot; // CAMBIO IMPORTANTE AQUÍ
// No necesitamos HasFactory ni SoftDeletes para este modelo Pivot por defecto,
// a menos que la tabla pivote misma tenga un campo 'deleted_at'.

class TrabajadorCondicion extends Pivot // CAMBIO IMPORTANTE AQUÍ
{
    // Indica que esta es la tabla pivote.
    protected $table = 'trabajador_condicion';

    // Si tu tabla pivote usa timestamps (created_at, updated_at), lo cual es el caso según la migración
    public $timestamps = true;

    // Laravel por defecto asume que las FKs en la tabla pivote son 'model_key'.
    // Como nuestras FKs son 'trabajador_id' y 'condicion_trabajador_id', no necesitamos forzar nombres aquí.
    // Las relaciones en los modelos Trabajador y CondicionTrabajadorMaestro ya lo manejan.

    // Campos que son parte de la tabla pivote y quieres que sean "fillable"
    // si alguna vez creas/actualizas registros de esta tabla pivote directamente
    // usando el modelo TrabajadorCondicion. Normalmente se hace a través de attach/sync/updateExistingPivot.
    protected $fillable = [
        'trabajador_id',
        'condicion_trabajador_id',
        'fecha_asignacion_condicion',
        'fecha_vencimiento_condicion',
        'valor_extra_condicion',
    ];

    protected $casts = [
        'fecha_asignacion_condicion' => 'date',
        'fecha_vencimiento_condicion' => 'date',
    ];

    // Opcionalmente, puedes definir las relaciones inversas desde el Pivot,
    // aunque raramente se usan directamente de esta manera.
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function condicionTrabajadorMaestro()
    {
        return $this->belongsTo(CondicionTrabajadorMaestro::class, 'condicion_trabajador_id');
    }
}