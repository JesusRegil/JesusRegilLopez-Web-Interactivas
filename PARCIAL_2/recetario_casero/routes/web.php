<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecetaController;
use Illuminate\Support\Facades\Route;

// La portada es el recetario; quien no ha iniciado sesión termina en /login.
Route::redirect('/', '/recetas');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/registro', [AuthController::class, 'showRegister'])->name('registro');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Solo ids numéricos: así PostgreSQL nunca recibe texto donde espera un número.
    Route::resource('recetas', RecetaController::class)
        ->parameters(['recetas' => 'receta'])
        ->where(['receta' => '[0-9]{1,18}']);
});