<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecetaRequest;
use App\Models\Receta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecetaController extends Controller
{
    /** Listado de MIS recetas, con buscador por título y filtro por categoría (combinables). */
    public function index(Request $request): View
    {
        $buscar = $request->query('q');
        $buscar = is_string($buscar) ? trim($buscar) : '';

        $categoria = $request->query('categoria');
        $categoria = is_string($categoria) && array_key_exists($categoria, Receta::CATEGORIAS) ? $categoria : '';

        $recetas = $request->user()->recetas()
            // Coincidencia parcial sin distinguir mayúsculas (ILIKE en PostgreSQL); se escapan % _ \
            ->when($buscar !== '', fn ($consulta) => $consulta->whereLike('titulo', '%'.addcslashes($buscar, '%_\\').'%'))
            ->when($categoria !== '', fn ($consulta) => $consulta->where('categoria', $categoria))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('recetas.index', [
            'recetas' => $recetas,
            'buscar' => $buscar,
            'categoria' => $categoria,
            'hayFiltros' => $buscar !== '' || $categoria !== '',
        ]);
    }

    public function create(): View
    {
        return view('recetas.create', ['receta' => new Receta]);
    }

    public function store(RecetaRequest $request): RedirectResponse
    {
        // user_id lo pone la relación: nunca viene del formulario.
        $request->user()->recetas()->create($request->validated());

        return redirect()->route('recetas.index')->with('exito', 'Receta creada correctamente.');
    }

    public function show(Receta $receta): View
    {
        return view('recetas.show', ['receta' => $receta]);
    }

    public function edit(Receta $receta): View
    {
        return view('recetas.edit', ['receta' => $receta]);
    }

    public function update(RecetaRequest $request, Receta $receta): RedirectResponse
    {
        $receta->update($request->validated());

        return redirect()->route('recetas.show', $receta)->with('exito', 'Receta actualizada correctamente.');
    }

    public function destroy(Receta $receta): RedirectResponse
    {
        $receta->delete();

        return redirect()->route('recetas.index')->with('exito', 'Receta eliminada correctamente.');
    }
}