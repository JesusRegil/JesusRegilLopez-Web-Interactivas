@extends('layouts.app')

@section('titulo', $receta->titulo)

@section('contenido')
    <article class="rounded border border-stone-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <h1 class="text-3xl font-bold">{{ $receta->titulo }}</h1>

            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('recetas.edit', $receta) }}" class="rounded border border-stone-300 px-3 py-1 hover:bg-stone-100">Editar</a>
                <form method="POST" action="{{ route('recetas.destroy', $receta) }}"
                      onsubmit="return confirm({{ Js::from('¿Eliminar la receta «'.$receta->titulo.'»? Esta acción no se puede deshacer.') }})">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded border border-red-300 px-3 py-1 text-red-600 hover:bg-red-50">Eliminar</button>
                </form>
            </div>
        </div>

        <dl class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-sm">
            <div><dt class="inline font-semibold">Categoría:</dt> <dd class="inline">{{ $receta->categoriaEtiqueta() }}</dd></div>
            <div><dt class="inline font-semibold">Tiempo:</dt> <dd class="inline">{{ $receta->tiempo_minutos }} min</dd></div>
            <div><dt class="inline font-semibold">Dificultad:</dt> <dd class="inline">{{ $receta->dificultadEtiqueta() }}</dd></div>
        </dl>

        <h2 class="mt-6 text-xl font-semibold">Ingredientes</h2>
        <ul class="mt-2 list-disc space-y-1 pl-6">
            @foreach ($receta->ingredientesLista() as $ingrediente)
                <li>{{ $ingrediente }}</li>
            @endforeach
        </ul>

        <h2 class="mt-6 text-xl font-semibold">Pasos de preparación</h2>
        <ol class="mt-2 list-decimal space-y-2 pl-6">
            @foreach ($receta->pasosLista() as $paso)
                <li>{{ $paso }}</li>
            @endforeach
        </ol>

        @if ($receta->nota)
            <h2 class="mt-6 text-xl font-semibold">Nota personal</h2>
            <p class="mt-2 whitespace-pre-line rounded bg-amber-50 p-3">{{ $receta->nota }}</p>
        @endif
    </article>

    <a href="{{ route('recetas.index') }}" class="mt-4 inline-block text-amber-700 hover:underline">← Volver a mis recetas</a>
@endsection