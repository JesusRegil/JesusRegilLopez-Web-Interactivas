<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Torneo;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    // Solo jugadores (el admin gestiona, no se inscribe)
    public function store(Torneo $torneo)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return back()->with('error', 'El administrador no puede inscribirse a torneos.');
        }

        return DB::transaction(function () use ($torneo, $user) {
            $torneo->loadCount('inscripciones');

            if ($torneo->inscripciones()->where('user_id', $user->id)->exists()) {
                return back()->with('error', 'Ya estás inscrito en este torneo.');
            }
            if ($torneo->haPasado()) {
                return back()->with('error', 'Este torneo ya se realizó, no puedes inscribirte.');
            }
            if ($torneo->estado === 'cerrado') {
                return back()->with('error', 'Este torneo está cerrado, no puedes inscribirte.');
            }
            if ($torneo->estaLleno()) {
                return back()->with('error', 'Este torneo está lleno, ya no hay plazas disponibles.');
            }

            Inscripcion::create(['user_id' => $user->id, 'torneo_id' => $torneo->id]);

            return back()->with('exito', '¡Te inscribiste a "'.$torneo->nombre.'"!');
        });
    }

    public function misTorneos()
    {
        $inscripciones = auth()->user()->inscripciones()
            ->with(['torneo' => fn ($q) => $q->withCount('inscripciones')])
            ->get()
            ->sortBy(fn ($i) => $i->torneo->fecha);

        return view('torneos.mis', compact('inscripciones'));
    }

    // El jugador cancela SU inscripción mientras el torneo no haya pasado
    public function destroy(Torneo $torneo)
    {
        $inscripcion = Inscripcion::where('user_id', auth()->id())
            ->where('torneo_id', $torneo->id)->first();

        if (! $inscripcion) {
            return back()->with('error', 'No estás inscrito en este torneo.');
        }
        if ($torneo->haPasado()) {
            return back()->with('error', 'El torneo ya se realizó, no puedes cancelar tu inscripción.');
        }

        $inscripcion->delete();

        return back()->with('exito', 'Cancelaste tu inscripción a "'.$torneo->nombre.'". La plaza quedó libre.');
    }
}