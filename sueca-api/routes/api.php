<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameEngineController; // <--- Importante!

// Rota pública
Route::post('login', [AuthController::class, 'login']);

// Rotas protegidas (precisam de Token)
Route::group(['middleware' => 'auth:api'], function () {
    // Iniciar Jogo
    Route::post('/game/{id}/start', [GameEngineController::class, 'startGame']);
    // Ver Estado (Mão, Mesa, Trunfo)
    Route::get('/game/{id}/state', [GameEngineController::class, 'getGameState']);
    // Jogar uma Carta [NOVO]
    Route::post('/game/{id}/play', [GameEngineController::class, 'playCard']);
});