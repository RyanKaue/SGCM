<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tipo',
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

    /**
     * Verifica se o usuário é um administrador
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->tipo === 'admin';
    }

    /**
     * Verifica se o usuário é um médico
     *
     * @return bool
     */
    public function isMedico()
    {
        return $this->tipo === 'medico';
    }

    /**
     * Verifica se o usuário é um paciente
     *
     * @return bool
     */
    public function isPaciente()
    {
        return $this->tipo === 'paciente';
    }

    /**
     * Relacionamento com o modelo Medico
     */
    public function medico()
    {
        return $this->hasOne(Medico::class);
    }

    /**
     * Relacionamento com o modelo Paciente
     */
    public function paciente()
    {
        return $this->hasOne(Paciente::class);
    }
}
