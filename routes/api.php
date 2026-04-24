<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas públicas (sem autenticação)
|--------------------------------------------------------------------------
|
| BUG INTENCIONAL: O endpoint de login está exigindo autenticação JWT.
| O candidato deve mover esta rota para fora do middleware 'jwt.auth'.
|
*/

Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rotas protegidas (requerem JWT)
|--------------------------------------------------------------------------
|
| BUG INTENCIONAL: As rotas de /api/users não possuem verificação de role.
| No projeto original Java, list/update/delete exigem ADMIN.
| O candidato deve adicionar o middleware de role adequado.
|
*/
Route::middleware(['jwt.auth'])->group(function () {

    // Users - CRUD (falta middleware de role)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Shows - A implementar pelo candidato
    // Route::post('/shows', [ShowController::class, 'store']);
    // Route::get('/shows', [ShowController::class, 'index']);

    // Episodes - A implementar pelo candidato
    // Route::get('/episodes/average', [EpisodeController::class, 'average']);
});
