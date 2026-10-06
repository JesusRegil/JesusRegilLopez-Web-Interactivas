<?php

namespace App\Http\Controllers;

use App\Models\Torneo;
use Illuminate\Http\Request;

// Consulta pública: cualquiera (incluso sin cuenta) puede ver torneos
class TorneoController extends Controller
{
    public function index(Request $request)
    {
        $query = Torneo::disponibles()->withCount('inscripciones');

        // Buscador (extra)
        if ($buscar = trim($request->query('q', ''))) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('juego', 'like', "%{$buscar}%");
            });
        }

        $torneos = $query->orderBy('fecha')->get();

        // IDs de torneos en los que el usuario ya está inscrito
        $inscritoEn = auth()->check()
            ? auth()->user()->inscripciones()->pluck('torneo_id')->all()
            : [];

        return view('torneos.index', compact('torneos', 'inscritoEn', 'buscar'));
    }

    // El detalle funciona incluso para torneos cerrados/llenos (acceso directo)
    public function show(Torneo $torneo)
    {
        $torneo->loadCount('inscripciones');
        $participantes = $torneo->participantes()->orderBy('inscripciones.created_at')->get();
        $yaInscrito = auth()->check() && $participantes->contains('id', auth()->id());

        return view('torneos.show', compact('torneo', 'participantes', 'yaInscrito'));
    }
}