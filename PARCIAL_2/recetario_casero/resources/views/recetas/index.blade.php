@extends('layouts.app')

@section('titulo', 'Mis recetas')

@section('contenido')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Mis recetas</h1>
        <a href="{{ route('recetas.create') }}" class="rounded bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700">Nueva receta</a>
    </div>

    {{-- Buscador por título + filtro por categoría (se pueden combinar) --}}
    <form method="GET" action="{{ route('recetas.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded border border-stone-200 bg-white p-4">
        <div class="min-w-48 grow">
            <label for="q" class="block text-sm font-medium">Buscar por título</label>
            <input type="text" id="q" name="q" value="{{ $buscar }}" placeholder="Por ejemplo: galletas"
                   class="mt-1 w-full rounded border border-stone-300 px-3 py-2">
        </div>

        <div>
            <label for="categoria" class="block text-sm font-medium">Categoría</label>
            <select id="categoria" name="categoria" class="mt-1 rounded border border-stone-300 px-3 py-2">
                <option value="">Todas</option>
                @foreach (\App\Models\Receta::CATEGORIAS as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected($categoria === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded bg-stone-800 px-4 py-2 font-semibold text-white hover:bg-stone-700">Buscar</button>
            <a href="{{ route('recetas.index') }}" class="text-sm text-stone-600 underline hover:text-stone-900">Limpiar</a>
        </div>
    </form>

    @if ($recetas->isEmpty())
        <div class="rounded border border-dashed border-stone-300 bg-white p-8 text-center">
            @if ($hayFiltros)
                <p class="text-lg font-semibold">No se encontraron recetas con esa búsqueda o categoría.</p>
                <a href="{{ route('recetas.index') }}" class="mt-4 inline-block rounded border border-stone-300 px-4 py-2 hover:bg-stone-100">Limpiar filtros</a>
            @else
                <p class="text-lg font-semibold">Aún no tienes recetas.</p>
                <a href="{{ route('recetas.create') }}" class="mt-4 inline-block rounded bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700">Crear mi primera receta</a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded border border-stone-200 bg-white">
            <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                <thead class="bg-stone-100 text-xs font-semibold uppercase tracking-wide text-stone-600">
                    <tr>
                        <th class="px-4 py-3">Título</th>
                        <th class="px-4 py-3">Categoría</th>
                        <th class="px-4 py-3">Tiempo</th>
                        <th class="px-4 py-3">Dificultad</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($recetas as $receta)
                        <tr>
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('recetas.show', $receta) }}" class="text-amber-700 hover:underline">{{ $receta->titulo }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $receta->categoriaEtiqueta() }}</td>
                            <td class="px-4 py-3">{{ $receta->tiempo_minutos }} min</td>
                            <td class="px-4 py-3">{{ $receta->dificultadEtiqueta() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('recetas.show', $receta) }}" class="text-stone-700 hover:underline">Ver</a>
                                <a href="{{ route('recetas.edit', $receta) }}" class="ml-3 text-stone-700 hover:underline">Editar</a>
                                <form method="POST" action="{{ route('recetas.destroy', $receta) }}" class="ml-3 inline"
                                      onsubmit="return confirm({{ Js::from('¿Eliminar la receta «'.$receta->titulo.'»? Esta acción no se puede deshacer.') }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection