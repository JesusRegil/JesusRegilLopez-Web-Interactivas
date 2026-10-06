<?php

use App\Http\Controllers\Admin\TorneoController as AdminTorneoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\TorneoController;
use Illuminate\Support\Facades\Route;

// ---- Público (invitados) ----
Route::get('/', [TorneoController::class, 'index'])->name('torneos.index');
Route::get('/torneos/{torneo}', [TorneoController::class, 'show'])->name('torneos.show');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ---- Usuarios con sesión ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/mis-torneos', [InscripcionController::class, 'misTorneos'])->name('mis-torneos');
    Route::post('/torneos/{torneo}/inscribirme', [InscripcionController::class, 'store'])->name('inscripciones.store');
    Route::delete('/torneos/{torneo}/inscripcion', [InscripcionController::class, 'destroy'])->name('inscripciones.destroy');
});

// ---- Solo administrador ----
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('torneos', AdminTorneoController::class)->except('show');
    Route::get('torneos/{torneo}/inscritos', [AdminTorneoController::class, 'inscritos'])->name('torneos.inscritos');
    Route::delete('torneos/{torneo}/inscritos/{inscripcion}', [AdminTorneoController::class, 'bajaInscripcion'])->name('torneos.baja');
});