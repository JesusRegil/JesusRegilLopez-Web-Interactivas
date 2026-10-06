<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Torneo;
use Illuminate\Http\Request;

class TorneoController extends Controller
{
    // Lista completa para el admin (todos los torneos, sin filtros)
    public function index()
    {
        $torneos = Torneo::withCount('inscripciones')->orderBy('fecha', 'desc')->get();

        return view('admin.index', compact('torneos'));
    }

    public function create()
    {
        return view('admin.form', ['torneo' => new Torneo(['cupo' => 16, 'estado' => 'abierto'])]);
    }

    public function store(Request $request)
    {
        Torneo::create($this->validar($request));

        return redirect()->route('admin.torneos.index')->with('exito', 'Torneo creado correctamente.');
    }

    public function edit(Torneo $torneo)
    {
        $torneo->loadCount('inscripciones');

        return view('admin.form', compact('torneo'));
    }

    public function update(Request $request, Torneo $torneo)
    {
        $inscritos = $torneo->inscripciones()->count();

        $datos = $this->validar($request, $inscritos);
        $torneo->update($datos);

        return redirect()->route('admin.torneos.index')->with('exito', 'Torneo actualizado correctamente.');
    }

    public function destroy(Torneo $torneo)
    {
        $torneo->delete(); // las inscripciones se borran en cascada

        return redirect()->route('admin.torneos.index')->with('exito', 'Torneo eliminado junto con sus inscripciones.');
    }

    // Ver inscritos del torneo
    public function inscritos(Torneo $torneo)
    {
        $torneo->loadCount('inscripciones');
        $inscripciones = $torneo->inscripciones()->with('user')->get();

        return view('admin.inscritos', compact('torneo', 'inscripciones'));
    }

    // Dar de baja una inscripción
    public function bajaInscripcion(Torneo $torneo, Inscripcion $inscripcion)
    {
        if ($inscripcion->torneo_id !== $torneo->id) {
            abort(404);
        }

        $nombre = $inscripcion->user->name;
        $inscripcion->delete();

        return back()->with('exito', "Se dio de baja a {$nombre}.");
    }

    private function validar(Request $request, int $inscritos = 0): array
    {

        return $request->validate([
            'nombre' => 'required|string|max:100',
            'juego' => 'required|string|max:100',
            'fecha' => 'required|date|after:now',
            'cupo' => ['required', 'integer', 'between:2,100', function ($attr, $value, $fail) use ($inscritos) {
                if ($value < $inscritos) {
                    $fail("El cupo no puede ser menor a los {$inscritos} jugadores ya inscritos.");
                }
            }],
            'descripcion' => 'nullable|string|max:1000',
            'estado' => 'required|in:abierto,cerrado',
        ]);
    }
}
