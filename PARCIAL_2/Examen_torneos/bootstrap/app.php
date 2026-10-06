<?php

use App\Http\Middleware\EsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin' => EsAdmin::class]);

        // Invitado que entra a una ruta protegida -> login con aviso
        $middleware->redirectGuestsTo(function () {
            session()->flash('error', 'Debes iniciar sesión para continuar.');
            return route('login');
        });
        // Usuario ya logueado que entra a login/registro -> inicio
        $middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();