<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data_nascimento',
        'cpf',
        'telefone',
        'endereco',
        'plano_saude',
        'observacoes',
    ];

    /**
     * Relacionamento com o modelo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o modelo Consulta
     */
    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }
}
