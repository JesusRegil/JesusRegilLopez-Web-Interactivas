@extends('layouts.app')
@section('titulo', 'Mis torneos')

@section('contenido')
<h1 class="h3 mb-3">Mis torneos</h1>

@forelse($inscripciones as $i)
    @php $torneo = $i->torneo; @endphp
    @if($loop->first)
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Torneo</th><th>Juego</th><th>Fecha</th><th>Estado</th><th></th></tr></thead><tbody>
    @endif
        <tr>
            <td><a href="{{ route('torneos.show', $torneo) }}">{{ $torneo->nombre }}</a></td>
            <td>{{ $torneo->juego }}</td>
            <td>{{ $torneo->fecha->format('d/m/Y H:i') }}</td>
            <td>@include('torneos._estado')</td>
            <td class="text-end">
                @if($torneo->haPasado())
                    <span class="text-muted small">Ya se realizó</span>
                @else
                    <form method="POST" action="{{ route('inscripciones.destroy', $torneo) }}"
                          onsubmit="return confirm('¿Seguro que quieres cancelar tu inscripción?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">Cancelar inscripción</button>
                    </form>
                @endif
            </td>
        </tr>
    @if($loop->last) </tbody></table></div></div> @endif
@empty
    <div class="alert alert-info">
        Aún no estás inscrito en ningún torneo.
        <a href="{{ route('torneos.index') }}">Ver torneos disponibles</a>
    </div>
@endforelse
@endsection