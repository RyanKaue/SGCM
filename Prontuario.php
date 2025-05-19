<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prontuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'consulta_id',
        'sintomas',
        'diagnostico',
        'receita',
        'observacoes',
    ];

    /**
     * Relacionamento com o modelo Consulta
     */
    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }
}
