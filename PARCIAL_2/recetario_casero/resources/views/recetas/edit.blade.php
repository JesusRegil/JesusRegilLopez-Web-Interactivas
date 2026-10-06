@extends('layouts.app')

@section('titulo', 'Editar receta')

@section('contenido')
    <h1 class="mb-4 text-2xl font-bold">Editar receta</h1>

    <form method="POST" action="{{ route('recetas.update', $receta) }}" novalidate class="space-y-4 rounded border border-stone-200 bg-white p-6">
        @csrf
        @method('PUT')

        @include('recetas._formulario')

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700">Guardar cambios</button>
            <a href="{{ route('recetas.show', $receta) }}" class="rounded border border-stone-300 px-4 py-2 hover:bg-stone-100">Cancelar</a>
        </div>
    </form>
@endsection