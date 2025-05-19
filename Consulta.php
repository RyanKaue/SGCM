<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'data',
        'hora',
        'status',
        'motivo',
    ];

    /**
     * Relacionamento com o modelo Medico
     */
    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    /**
     * Relacionamento com o modelo Paciente
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Relacionamento com o modelo Prontuario
     */
    public function prontuario()
    {
        return $this->hasOne(Prontuario::class);
    }
}
