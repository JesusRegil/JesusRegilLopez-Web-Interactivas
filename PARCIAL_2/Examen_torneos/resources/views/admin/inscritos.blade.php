@extends('layouts.app')
@section('titulo', 'Inscritos')

@section('contenido')
<a href="{{ route('admin.torneos.index') }}" class="text-decoration-none">&larr; Volver a torneos</a>
<h1 class="h3 mt-2">Inscritos en "{{ $torneo->nombre }}"</h1>
<p class="text-muted">{{ $torneo->inscripciones_count }} / {{ $torneo->cupo }} plazas ocupadas</p>

@if($inscripciones->isEmpty())
    <div class="alert alert-info">Este torneo aún no tiene inscritos.</div>
@else
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Jugador</th><th>Correo</th><th>Inscrito el</th><th></th></tr></thead>
    <tbody>
    @foreach($inscripciones as $i)
        <tr>
            <td>{{ $i->user->name }}</td>
            <td>{{ $i->user->email }}</td>
            <td>{{ $i->created_at->format('d/m/Y H:i') }}</td>
            <td class="text-end">
                <form method="POST" action="{{ route('admin.torneos.baja', [$torneo, $i]) }}"
                      onsubmit="return confirm('¿Dar de baja a este jugador?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Dar de baja</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div></div>
@endif
@endsection