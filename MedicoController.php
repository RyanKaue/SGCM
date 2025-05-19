<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MedicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $medicos = Medico::with(['user', 'especialidade'])->get();
        return response()->json($medicos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'crm' => 'required|string|max:20|unique:medicos',
            'especialidade_id' => 'nullable|exists:especialidades,id',
            'horario_inicio' => 'nullable|date_format:H:i',
            'horario_fim' => 'nullable|date_format:H:i|after:horario_inicio',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tipo' => 'medico',
            ]);

            $medico = Medico::create([
                'user_id' => $user->id,
                'crm' => $request->crm,
                'especialidade_id' => $request->especialidade_id,
                'horario_inicio' => $request->horario_inicio ?? '08:00',
                'horario_fim' => $request->horario_fim ?? '18:00',
                'ativo' => true,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Médico cadastrado com sucesso',
                'medico' => $medico->load('user', 'especialidade')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao cadastrar médico',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $medico = Medico::with(['user', 'especialidade'])->findOrFail($id);
        return response()->json($medico);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $medico = Medico::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($medico->user_id),
            ],
            'crm' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('medicos')->ignore($id),
            ],
            'especialidade_id' => 'nullable|exists:especialidades,id',
            'horario_inicio' => 'nullable|date_format:H:i',
            'horario_fim' => 'nullable|date_format:H:i|after:horario_inicio',
            'ativo' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('name') || $request->has('email')) {
                $user = User::find($medico->user_id);
                $userData = [];
                
                if ($request->has('name')) {
                    $userData['name'] = $request->name;
                }
                
                if ($request->has('email')) {
                    $userData['email'] = $request->email;
                }
                
                $user->update($userData);
            }

            $medicoData = [];
            if ($request->has('crm')) {
                $medicoData['crm'] = $request->crm;
            }
            
            if ($request->has('especialidade_id')) {
                $medicoData['especialidade_id'] = $request->especialidade_id;
            }
            
            if ($request->has('horario_inicio')) {
                $medicoData['horario_inicio'] = $request->horario_inicio;
            }
            
            if ($request->has('horario_fim')) {
                $medicoData['horario_fim'] = $request->horario_fim;
            }
            
            if ($request->has('ativo')) {
                $medicoData['ativo'] = $request->ativo;
            }
            
            if (!empty($medicoData)) {
                $medico->update($medicoData);
            }

            DB::commit();
            return response()->json([
                'message' => 'Médico atualizado com sucesso',
                'medico' => $medico->fresh()->load('user', 'especialidade')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar médico',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $medico = Medico::findOrFail($id);
        $userId = $medico->user_id;

        DB::beginTransaction();
        try {
            $medico->delete();
            User::destroy($userId);
            
            DB::commit();
            return response()->json([
                'message' => 'Médico removido com sucesso'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao remover médico',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
