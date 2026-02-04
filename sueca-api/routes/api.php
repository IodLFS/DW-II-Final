<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameEngineController;

// Rota de Login (Pública)
Route::post('login', [AuthController::class, 'login']);

// Rotas de Jogo (Protegidas com Token)
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/game/{id}/start', [GameEngineController::class, 'startGame']);
    Route::get('/game/{id}/state', [GameEngineController::class, 'getGameState']);
    Route::post('/game/{id}/play', [GameEngineController::class, 'playCard']);
});