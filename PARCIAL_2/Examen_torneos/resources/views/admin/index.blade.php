@extends('layouts.app')
@section('titulo', 'Administrar torneos')

@section('contenido')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Administrar torneos</h1>
    <a href="{{ route('admin.torneos.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo torneo</a>
</div>

@if($torneos->isEmpty())
    <div class="alert alert-info">Todavía no hay torneos. Crea el primero.</div>
@else
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Nombre</th><th>Juego</th><th>Fecha</th><th>Inscritos</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
    <tbody>
    @foreach($torneos as $torneo)
        <tr>
            <td><a href="{{ route('torneos.show', $torneo) }}">{{ $torneo->nombre }}</a></td>
            <td>{{ $torneo->juego }}</td>
            <td>{{ $torneo->fecha->format('d/m/Y H:i') }}</td>
            <td>{{ $torneo->inscritos() }} / {{ $torneo->cupo }}</td>
            <td>@include('torneos._estado')</td>
            <td class="text-end text-nowrap">
                <a href="{{ route('admin.torneos.inscritos', $torneo) }}" class="btn btn-outline-secondary btn-sm">Inscritos</a>
                <a href="{{ route('admin.torneos.edit', $torneo) }}" class="btn btn-outline-primary btn-sm">Editar</a>
                <form method="POST" action="{{ route('admin.torneos.destroy', $torneo) }}" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este torneo y todas sus inscripciones?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div></div>
@endif
@endsection