<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Especialidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EspecialidadeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $especialidades = Especialidade::all();
        return response()->json($especialidades);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Apenas administradores podem criar especialidades
        if ($user->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $request->validate([
            'nome' => 'required|string|max:255|unique:especialidades',
            'descricao' => 'nullable|string',
            'ativa' => 'sometimes|boolean',
        ]);
        
        $especialidade = Especialidade::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'ativa' => $request->ativa ?? true,
        ]);
        
        return response()->json([
            'message' => 'Especialidade criada com sucesso',
            'especialidade' => $especialidade
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $especialidade = Especialidade::findOrFail($id);
        return response()->json($especialidade);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        
        // Apenas administradores podem atualizar especialidades
        if ($user->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $especialidade = Especialidade::findOrFail($id);
        
        $request->validate([
            'nome' => 'sometimes|required|string|max:255|unique:especialidades,nome,' . $id,
            'descricao' => 'nullable|string',
            'ativa' => 'sometimes|boolean',
        ]);
        
        $especialidade->update($request->all());
        
        return response()->json([
            'message' => 'Especialidade atualizada com sucesso',
            'especialidade' => $especialidade->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        
        // Apenas administradores podem excluir especialidades
        if ($user->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $especialidade = Especialidade::findOrFail($id);
        
        // Verificar se há médicos vinculados a esta especialidade
        if ($especialidade->medicos()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir esta especialidade pois existem médicos vinculados a ela'
            ], 422);
        }
        
        $especialidade->delete();
        
        return response()->json([
            'message' => 'Especialidade removida com sucesso'
        ]);
    }
}
