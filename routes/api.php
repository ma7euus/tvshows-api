<?php

use App\Enums\Role;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Shows\ShowController;
use App\Http\Controllers\Shows\ShowImportController;
use App\Http\Controllers\Shows\EpisodeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas públicas (sem autenticação)
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Rotas protegidas (requerem JWT)
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.auth'])->group(function () {

    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);

    Route::get('/shows', [ShowController::class, 'index']);
    Route::get('/episodes/average', [EpisodeController::class, 'average']);

    Route::middleware([Role::ADMIN->middleware()])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/shows', [ShowController::class, 'store']);

        /*
        |--------------------------------------------------------------------------
        | Importação assíncrona de shows
        |--------------------------------------------------------------------------
        */
        Route::post('/shows/imports/paginated', [ShowImportController::class, 'store']);
        Route::get('/shows/imports/{import}', [ShowImportController::class, 'show']);
        Route::post('/shows/imports/{import}/resume', [ShowImportController::class, 'resume']);
    });
});
