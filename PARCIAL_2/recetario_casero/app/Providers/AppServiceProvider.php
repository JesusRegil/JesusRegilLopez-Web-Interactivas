<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // URLs en español: /recetas/crear y /recetas/{receta}/editar
        Route::resourceVerbs(['create' => 'crear', 'edit' => 'editar']);

        // {receta} se busca SOLO entre las recetas del usuario con sesión iniciada:
        // si la receta es de otra persona (o no existe) la respuesta es 404.
        Route::bind('receta', fn (string $id) => request()->user()->recetas()->findOrFail($id));
    }
}