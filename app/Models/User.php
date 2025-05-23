<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // Asegúrate que esta línea esté

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // Asegúrate que HasRoles esté aquí

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- INICIO DE LAS NUEVAS RELACIONES ASEM ---

    /**
     * La empresa contratista que este usuario administra (si aplica).
     * Esta es la relación inversa a EmpresaContratista::user().
     */
    public function empresaContratistaAdministrada()
    {
        return $this->hasOne(EmpresaContratista::class, 'user_id');
    }

    /**
     * Documentos adjuntos que fueron subidos por este usuario.
     */
    public function documentosSubidos()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'subido_por_user_id');
    }

    /**
     * Documentos adjuntos que fueron validados por este usuario (Analista ASEM).
     */
    public function documentosValidados()
    {
        return $this->hasMany(DocumentoAdjunto::class, 'validado_por_user_id');
    }
    // --- FIN DE LAS NUEVAS RELACIONES ASEM ---
}