<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'apellido_paterno',
        'apellido_materno',
        'genero',
        'email',
        'password',
        'qr_secret',
        'is_active',
        'ruta_foto_perfil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'qr_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class);
    }

    public function visitante()
    {
        return $this->hasOne(Visitante::class);
    }

    public function canAccessAdminPanel(Panel $panel): bool
    {
        // Permitir acceso al panel de administración solo si el usuario tiene el rol de administrador o verificador
        return $this->hasRole(['admin', 'verificador']) && $panel->getId() === 'admin';
    }
}
