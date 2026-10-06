@extends('layouts.app')
@section('titulo', $torneo->nombre)

@section('contenido')
<a href="{{ route('torneos.index') }}" class="text-decoration-none">&larr; Volver al listado</a>

<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <h1 class="h3">{{ $torneo->nombre }}</h1>
            <div>@include('torneos._estado')</div>
        </div>
        <dl class="row mb-3">
            <dt class="col-sm-3">Juego o deporte</dt><dd class="col-sm-9">{{ $torneo->juego }}</dd>
            <dt class="col-sm-3">Fecha</dt><dd class="col-sm-9">{{ $torneo->fecha->format('d/m/Y H:i') }}</dd>
            <dt class="col-sm-3">Cupo</dt>
            <dd class="col-sm-9">{{ $torneo->inscritos() }} / {{ $torneo->cupo }} ({{ $torneo->plazasLibres() }} plazas libres)</dd>
            <dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">{{ $torneo->descripcion ?: 'Sin descripción.' }}</dd>
        </dl>
        @include('torneos._acciones')
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h2 class="h5">Participantes ({{ $participantes->count() }})</h2>
        @if($participantes->isEmpty())
            <p class="text-muted mb-0">Aún no hay jugadores inscritos.</p>
        @else
            <ol class="mb-0">
                @foreach($participantes as $p)
                    <li>{{ $p->name }}</li>
                @endforeach
            </ol>
        @endif
    </div>
</div>
@endsection