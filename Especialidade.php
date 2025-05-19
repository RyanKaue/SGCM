<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'ativa',
    ];

    /**
     * Relacionamento com o modelo Medico
     */
    public function medicos()
    {
        return $this->hasMany(Medico::class);
    }
}
