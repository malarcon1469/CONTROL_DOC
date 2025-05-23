<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaMandante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empresas_mandantes';

    protected $fillable = [
        'rut_empresa_mandante',
        'nombre_empresa_mandante',
        'razon_social_mandante',
        'direccion_mandante',
        'ciudad_mandante',
        'telefono_mandante',
        'email_mandante',
        'nombre_contacto_mandante',
        'email_contacto_mandante',
        'telefono_contacto_mandante',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * Cargos específicos definidos por esta empresa mandante.
     */
    public function cargos()
    {
        // El modelo Cargo aún no se ha creado.
        return $this->hasMany(Cargo::class, 'empresa_mandante_id');
    }

    /**
     * Vinculaciones jerárquicas definidas por esta empresa mandante.
     */
    public function vinculaciones()
    {
        // El modelo Vinculacion aún no se ha creado.
        return $this->hasMany(Vinculacion::class, 'empresa_mandante_id');
    }

    /**
     * Configuraciones de documentos definidas por esta empresa mandante.
     */
    public function configuracionesDocumentosMandante()
    {
        // El modelo ConfiguracionDocumentoMandante aún no se ha creado.
        return $this->hasMany(ConfiguracionDocumentoMandante::class, 'empresa_mandante_id');
    }
}