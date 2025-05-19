<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MedicoController;
use App\Http\Controllers\API\PacienteController;
use App\Http\Controllers\API\ConsultaController;
use App\Http\Controllers\API\ProntuarioController;
use App\Http\Controllers\API\EspecialidadeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rotas para médicos
    Route::apiResource('medicos', MedicoController::class);
    
    // Rotas para pacientes
    Route::apiResource('pacientes', PacienteController::class);
    
    // Rotas para consultas
    Route::apiResource('consultas', ConsultaController::class);
    
    // Rotas para prontuários
    Route::apiResource('prontuarios', ProntuarioController::class);
    
    // Rotas para especialidades
    Route::apiResource('especialidades', EspecialidadeController::class);
});
