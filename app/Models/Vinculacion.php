<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vinculacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vinculaciones';

    protected $fillable = [
        'empresa_mandante_id',
        'nombre_vinculacion',
        'descripcion_vinculacion',
        'parent_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Empresa mandante que definió esta vinculación (si es específica del mandante).
     */
    public function empresaMandante()
    {
        return $this->belongsTo(EmpresaMandante::class, 'empresa_mandante_id');
    }

    /**
     * Vinculación padre (para jerarquías).
     */
    public function parent()
    {
        return $this->belongsTo(Vinculacion::class, 'parent_id');
    }

    /**
     * Vinculaciones hijas (para jerarquías).
     */
    public function children()
    {
        return $this->hasMany(Vinculacion::class, 'parent_id');
    }

    /**
     * Configuraciones de documentos mandante que aplican a esta vinculación.
     */
    public function configuracionesDocumentosMandante()
    {
        // El modelo ConfiguracionDocumentoMandante aún no se ha creado.
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'vinculacion_id');
    }
}