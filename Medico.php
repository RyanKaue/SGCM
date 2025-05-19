<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crm',
        'especialidade_id',
        'horario_inicio',
        'horario_fim',
        'ativo',
    ];

    /**
     * Relacionamento com o modelo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o modelo Especialidade
     */
    public function especialidade()
    {
        return $this->belongsTo(Especialidade::class);
    }

    /**
     * Relacionamento com o modelo Consulta
     */
    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }
}
