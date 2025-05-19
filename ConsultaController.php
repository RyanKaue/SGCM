<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $consultas = null;
        
        // Filtra consultas de acordo com o tipo de usuário
        if ($user->tipo === 'admin') {
            $consultas = Consulta::with(['medico.user', 'paciente.user'])->get();
        } elseif ($user->tipo === 'medico') {
            $medico = Medico::where('user_id', $user->id)->first();
            if ($medico) {
                $consultas = Consulta::with(['paciente.user'])
                    ->where('medico_id', $medico->id)
                    ->get();
            }
        } elseif ($user->tipo === 'paciente') {
            $paciente = Paciente::where('user_id', $user->id)->first();
            if ($paciente) {
                $consultas = Consulta::with(['medico.user'])
                    ->where('paciente_id', $paciente->id)
                    ->get();
            }
        }
        
        return response()->json($consultas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'medico_id' => 'required|exists:medicos,id',
            'data' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'motivo' => 'nullable|string',
        ]);

        // Verificar se o médico está disponível nesse horário
        $medico = Medico::findOrFail($request->medico_id);
        
        // Verificar se o horário está dentro do horário de trabalho do médico
        if ($request->hora < $medico->horario_inicio || $request->hora > $medico->horario_fim) {
            return response()->json([
                'message' => 'Horário fora do período de atendimento do médico'
            ], 422);
        }
        
        // Verificar se já existe consulta agendada para o médico nesse horário
        $consultaExistente = Consulta::where('medico_id', $request->medico_id)
            ->where('data', $request->data)
            ->where('hora', $request->hora)
            ->where('status', '!=', 'cancelada')
            ->exists();
            
        if ($consultaExistente) {
            return response()->json([
                'message' => 'Já existe uma consulta agendada para este médico neste horário'
            ], 422);
        }
        
        $consulta = Consulta::create([
            'paciente_id' => $request->paciente_id,
            'medico_id' => $request->medico_id,
            'data' => $request->data,
            'hora' => $request->hora,
            'motivo' => $request->motivo,
            'status' => 'agendada',
        ]);
        
        return response()->json([
            'message' => 'Consulta agendada com sucesso',
            'consulta' => $consulta->load(['medico.user', 'paciente.user'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $consulta = Consulta::with(['medico.user', 'paciente.user'])->findOrFail($id);
        
        // Verifica permissão de acesso
        if ($user->tipo === 'medico') {
            $medico = Medico::where('user_id', $user->id)->first();
            if (!$medico || $consulta->medico_id !== $medico->id) {
                return response()->json(['message' => 'Acesso não autorizado'], 403);
            }
        } elseif ($user->tipo === 'paciente') {
            $paciente = Paciente::where('user_id', $user->id)->first();
            if (!$paciente || $consulta->paciente_id !== $paciente->id) {
                return response()->json(['message' => 'Acesso não autorizado'], 403);
            }
        }
        
        return response()->json($consulta);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $consulta = Consulta::findOrFail($id);
        $user = Auth::user();
        
        // Verifica permissão de acesso
        if ($user->tipo === 'paciente') {
            $paciente = Paciente::where('user_id', $user->id)->first();
            if (!$paciente || $consulta->paciente_id !== $paciente->id) {
                return response()->json(['message' => 'Acesso não autorizado'], 403);
            }
            
            // Paciente só pode cancelar consulta
            if ($request->status && $request->status !== 'cancelada') {
                return response()->json(['message' => 'Operação não permitida'], 403);
            }
        } elseif ($user->tipo === 'medico') {
            $medico = Medico::where('user_id', $user->id)->first();
            if (!$medico || $consulta->medico_id !== $medico->id) {
                return response()->json(['message' => 'Acesso não autorizado'], 403);
            }
        }
        
        $request->validate([
            'status' => 'sometimes|required|in:agendada,confirmada,realizada,cancelada',
            'data' => 'sometimes|required|date|after_or_equal:today',
            'hora' => 'sometimes|required|date_format:H:i',
            'motivo' => 'nullable|string',
        ]);
        
        // Se estiver alterando data/hora, verificar disponibilidade
        if (($request->has('data') || $request->has('hora')) && $request->status !== 'cancelada') {
            $data = $request->data ?? $consulta->data;
            $hora = $request->hora ?? $consulta->hora;
            
            // Verificar se o médico está disponível nesse horário
            $medico = Medico::findOrFail($consulta->medico_id);
            
            // Verificar se o horário está dentro do horário de trabalho do médico
            if ($hora < $medico->horario_inicio || $hora > $medico->horario_fim) {
                return response()->json([
                    'message' => 'Horário fora do período de atendimento do médico'
                ], 422);
            }
            
            // Verificar se já existe consulta agendada para o médico nesse horário
            $consultaExistente = Consulta::where('medico_id', $consulta->medico_id)
                ->where('data', $data)
                ->where('hora', $hora)
                ->where('status', '!=', 'cancelada')
                ->where('id', '!=', $id)
                ->exists();
                
            if ($consultaExistente) {
                return response()->json([
                    'message' => 'Já existe uma consulta agendada para este médico neste horário'
                ], 422);
            }
        }
        
        $consulta->update($request->all());
        
        return response()->json([
            'message' => 'Consulta atualizada com sucesso',
            'consulta' => $consulta->fresh()->load(['medico.user', 'paciente.user'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        // Apenas administradores podem excluir consultas
        if ($user->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $consulta = Consulta::findOrFail($id);
        $consulta->delete();
        
        return response()->json([
            'message' => 'Consulta removida com sucesso'
        ]);
    }
}
