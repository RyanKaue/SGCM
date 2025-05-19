<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pacientes = Paciente::with('user')->get();
        return response()->json($pacientes);
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
            'data_nascimento' => 'nullable|date',
            'cpf' => 'nullable|string|max:14|unique:pacientes',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'plano_saude' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'tipo' => 'paciente',
            ]);

            $paciente = Paciente::create([
                'user_id' => $user->id,
                'data_nascimento' => $request->data_nascimento,
                'cpf' => $request->cpf,
                'telefone' => $request->telefone,
                'endereco' => $request->endereco,
                'plano_saude' => $request->plano_saude,
                'observacoes' => $request->observacoes,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Paciente cadastrado com sucesso',
                'paciente' => $paciente->load('user')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao cadastrar paciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paciente = Paciente::with('user')->findOrFail($id);
        return response()->json($paciente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $paciente = Paciente::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($paciente->user_id),
            ],
            'data_nascimento' => 'nullable|date',
            'cpf' => [
                'nullable',
                'string',
                'max:14',
                Rule::unique('pacientes')->ignore($id),
            ],
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'plano_saude' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('name') || $request->has('email')) {
                $user = User::find($paciente->user_id);
                $userData = [];
                
                if ($request->has('name')) {
                    $userData['name'] = $request->name;
                }
                
                if ($request->has('email')) {
                    $userData['email'] = $request->email;
                }
                
                $user->update($userData);
            }

            $pacienteData = [];
            if ($request->has('data_nascimento')) {
                $pacienteData['data_nascimento'] = $request->data_nascimento;
            }
            
            if ($request->has('cpf')) {
                $pacienteData['cpf'] = $request->cpf;
            }
            
            if ($request->has('telefone')) {
                $pacienteData['telefone'] = $request->telefone;
            }
            
            if ($request->has('endereco')) {
                $pacienteData['endereco'] = $request->endereco;
            }
            
            if ($request->has('plano_saude')) {
                $pacienteData['plano_saude'] = $request->plano_saude;
            }
            
            if ($request->has('observacoes')) {
                $pacienteData['observacoes'] = $request->observacoes;
            }
            
            if (!empty($pacienteData)) {
                $paciente->update($pacienteData);
            }

            DB::commit();
            return response()->json([
                'message' => 'Paciente atualizado com sucesso',
                'paciente' => $paciente->fresh()->load('user')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar paciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paciente = Paciente::findOrFail($id);
        $userId = $paciente->user_id;

        DB::beginTransaction();
        try {
            $paciente->delete();
            User::destroy($userId);
            
            DB::commit();
            return response()->json([
                'message' => 'Paciente removido com sucesso'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao remover paciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
