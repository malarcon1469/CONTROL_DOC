<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cargos';

    protected $fillable = [
        'empresa_mandante_id',
        'nombre_cargo',
        'descripcion_cargo',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Empresa mandante que definió este cargo (si es específico del mandante).
     */
    public function empresaMandante()
    {
        return $this->belongsTo(EmpresaMandante::class, 'empresa_mandante_id');
    }

    /**
     * Configuraciones de documentos mandante que aplican a este cargo.
     */
    public function configuracionesDocumentosMandante()
    {
        // El modelo ConfiguracionDocumentoMandante aún no se ha creado.
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'cargo_id');
    }

    // Si se decide que los trabajadores tienen un cargo asignado directamente,
    // podríamos añadir:
    // public function trabajadores() { return $this->hasMany(Trabajador::class); }
    // Esto requeriría una FK 'cargo_id' en la tabla 'trabajadores'.
}