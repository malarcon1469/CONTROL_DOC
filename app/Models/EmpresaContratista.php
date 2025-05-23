<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaContratista extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empresas_contratistas';

    protected $fillable = [
        'rut_empresa_contratista',
        'nombre_empresa_contratista',
        'razon_social_contratista',
        'direccion_contratista',
        'ciudad_contratista',
        'telefono_contratista',
        'email_contratista',
        'nombre_representante_legal',
        'rut_representante_legal',
        'email_representante_legal',
        'telefono_representante_legal',
        'activa',
        'user_id',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Usuario de Laravel asociado a esta empresa contratista (si es un login).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Trabajadores que pertenecen a esta empresa contratista.
     */
    public function trabajadores()
    {
        // El modelo Trabajador aún no se ha creado.
        return $this->hasMany(Trabajador::class, 'empresa_contratista_id');
    }

    /**
     * Vehículos que pertenecen a esta empresa contratista.
     */
    public function vehiculos()
    {
        // El modelo Vehiculo aún no se ha creado.
        return $this->hasMany(Vehiculo::class, 'empresa_contratista_id');
    }

    /**
     * Condiciones (del maestro) que esta empresa contratista cumple.
     */
    public function condicionesContratistaMaestro()
    {
        // El modelo CondicionContratistaMaestro ya fue definido.
        return $this->belongsToMany(
            CondicionContratistaMaestro::class,
            'empresa_contratista_condicion',
            'empresa_contratista_id',
            'condicion_contratista_id'
        )->withTimestamps();
    }

    /**
     * Documentos adjuntos directamente a la empresa (usando relación polimórfica).
     */
    public function documentosAdjuntos()
    {
        // El modelo DocumentoAdjunto aún no se ha creado.
        // 'documentable' es el prefijo para la relación polimórfica en la tabla documentos_adjuntos
        return $this->morphMany(DocumentoAdjunto::class, 'documentable');
    }
}