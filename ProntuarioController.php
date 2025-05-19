<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\Prontuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Medico;

class ProntuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Apenas médicos e administradores podem listar prontuários
        if ($user->tipo === 'paciente') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $prontuarios = null;
        
        if ($user->tipo === 'admin') {
            $prontuarios = Prontuario::with(['consulta.medico.user', 'consulta.paciente.user'])->get();
        } elseif ($user->tipo === 'medico') {
            $medico = Medico::where('user_id', $user->id)->first();
            if ($medico) {
                $prontuarios = Prontuario::whereHas('consulta', function ($query) use ($medico) {
                    $query->where('medico_id', $medico->id);
                })->with(['consulta.paciente.user'])->get();
            }
        }
        
        return response()->json($prontuarios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Apenas médicos podem criar prontuários
        if ($user->tipo !== 'medico') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $request->validate([
            'consulta_id' => 'required|exists:consultas,id',
            'sintomas' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'receita' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);
        
        // Verificar se o médico é o responsável pela consulta
        $consulta = Consulta::findOrFail($request->consulta_id);
        $medico = Medico::where('user_id', $user->id)->first();
        
        if (!$medico || $consulta->medico_id !== $medico->id) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        // Verificar se a consulta já tem prontuário
        $prontuarioExistente = Prontuario::where('consulta_id', $request->consulta_id)->exists();
        if ($prontuarioExistente) {
            return response()->json([
                'message' => 'Esta consulta já possui um prontuário'
            ], 422);
        }
        
        // Verificar se a consulta foi realizada
        if ($consulta->status !== 'realizada') {
            // Atualizar status da consulta para realizada
            $consulta->update(['status' => 'realizada']);
        }
        
        $prontuario = Prontuario::create([
            'consulta_id' => $request->consulta_id,
            'sintomas' => $request->sintomas,
            'diagnostico' => $request->diagnostico,
            'receita' => $request->receita,
            'observacoes' => $request->observacoes,
        ]);
        
        return response()->json([
            'message' => 'Prontuário criado com sucesso',
            'prontuario' => $prontuario->load(['consulta.medico.user', 'consulta.paciente.user'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $prontuario = Prontuario::with(['consulta.medico.user', 'consulta.paciente.user'])->findOrFail($id);
        
        // Verificar permissão de acesso
        if ($user->tipo === 'medico') {
            $medico = Medico::where('user_id', $user->id)->first();
            if (!$medico || $prontuario->consulta->medico_id !== $medico->id) {
                return response()->json(['message' => 'Acesso não autorizado'], 403);
            }
        } elseif ($user->tipo === 'paciente') {
            $pacienteId = $prontuario->consulta->paciente_id;
            $paciente = Auth::user()->paciente;
            if (!$paciente || $paciente->id !== $pacienteId) {
                return response()->json(['message' => 'Acesso não autorizado'], 403);
            }
        }
        
        return response()->json($prontuario);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Apenas médicos podem atualizar prontuários
        if ($user->tipo !== 'medico') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $prontuario = Prontuario::findOrFail($id);
        
        // Verificar se o médico é o responsável pela consulta
        $medico = Medico::where('user_id', $user->id)->first();
        if (!$medico || $prontuario->consulta->medico_id !== $medico->id) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $request->validate([
            'sintomas' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'receita' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);
        
        $prontuario->update($request->all());
        
        return response()->json([
            'message' => 'Prontuário atualizado com sucesso',
            'prontuario' => $prontuario->fresh()->load(['consulta.medico.user', 'consulta.paciente.user'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        // Apenas administradores podem excluir prontuários
        if ($user->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $prontuario = Prontuario::findOrFail($id);
        $prontuario->delete();
        
        return response()->json([
            'message' => 'Prontuário removido com sucesso'
        ]);
    }
}
