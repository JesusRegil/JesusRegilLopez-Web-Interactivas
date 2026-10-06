@extends('layouts.app')
@section('titulo', 'Torneos disponibles')

@section('contenido')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">Torneos disponibles</h1>
    <form method="GET" class="d-flex gap-2">
        <input type="search" name="q" value="{{ $buscar }}" class="form-control" placeholder="Buscar por nombre o juego">
        <button class="btn btn-primary"><i class="bi bi-search"></i></button>
    </form>
</div>

@forelse($torneos as $torneo)
    @if($loop->first) <div class="row g-3"> @endif
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h5 class="card-title">{{ $torneo->nombre }}</h5>
                    @include('torneos._estado')
                </div>
                <p class="text-muted mb-1"><i class="bi bi-controller"></i> {{ $torneo->juego }}</p>
                <p class="mb-1"><i class="bi bi-calendar-event"></i> {{ $torneo->fecha->format('d/m/Y H:i') }}</p>
                <p class="mb-0"><i class="bi bi-people"></i> {{ $torneo->inscritos() }} / {{ $torneo->cupo }}
                    ({{ $torneo->plazasLibres() }} plazas libres)</p>
            </div>
            <div class="card-footer bg-white border-0 d-flex gap-2 flex-wrap">
                <a href="{{ route('torneos.show', $torneo) }}" class="btn btn-outline-primary btn-sm">Ver detalle</a>
                @include('torneos._acciones', ['yaInscrito' => in_array($torneo->id, $inscritoEn)])
            </div>
        </div>
    </div>
    @if($loop->last) </div> @endif
@empty
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        {{ $buscar ? 'No se encontraron torneos con esa búsqueda.' : 'No hay torneos disponibles por el momento.' }}
    </div>
@endforelse
@endsection