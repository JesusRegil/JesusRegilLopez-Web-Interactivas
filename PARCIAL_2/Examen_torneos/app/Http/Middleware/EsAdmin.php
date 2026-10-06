<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            return redirect()->route('torneos.index')
                ->with('error', 'No tienes permiso para entrar a esa sección. Solo el administrador puede hacerlo.');
        }

        return $next($request);
    }
}