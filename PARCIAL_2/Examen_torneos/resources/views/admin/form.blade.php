@extends('layouts.app')
@section('titulo', $torneo->exists ? 'Editar torneo' : 'Nuevo torneo')

@section('contenido')
<div class="row justify-content-center"><div class="col-lg-7">
<div class="card"><div class="card-body">
    <h1 class="h4 mb-3">{{ $torneo->exists ? 'Editar torneo' : 'Nuevo torneo' }}</h1>

    <form method="POST" novalidate
          action="{{ $torneo->exists ? route('admin.torneos.update', $torneo) : route('admin.torneos.store') }}">
        @csrf
        @if($torneo->exists) @method('PUT') @endif

        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" value="{{ old('nombre', $torneo->nombre) }}" class="form-control @error('nombre') is-invalid @enderror">
            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Juego o deporte *</label>
            <input type="text" name="juego" value="{{ old('juego', $torneo->juego) }}" class="form-control @error('juego') is-invalid @enderror">
            @error('juego')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row">
            <div class="col-md-7 mb-3">
                <label class="form-label">Fecha y hora * (debe ser futura)</label>
                <input type="datetime-local" name="fecha"
                       value="{{ old('fecha', $torneo->fecha?->format('Y-m-d\TH:i')) }}"
                       class="form-control @error('fecha') is-invalid @enderror">
                @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5 mb-3">
                <label class="form-label">Cupo * (2 a 100)</label>
                <input type="number" name="cupo" value="{{ old('cupo', $torneo->cupo) }}" class="form-control @error('cupo') is-invalid @enderror">
                @error('cupo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción (opcional)</label>
            <textarea name="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $torneo->descripcion) }}</textarea>
            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Estado *</label>
            <select name="estado" class="form-select @error('estado') is-invalid @enderror">
                @foreach(['abierto' => 'Abierto', 'cerrado' => 'Cerrado'] as $valor => $texto)
                    <option value="{{ $valor }}" @selected(old('estado', $torneo->estado) === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
            @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button class="btn btn-primary">Guardar</button>
        <a href="{{ route('admin.torneos.index') }}" class="btn btn-link">Cancelar</a>
    </form>
</div></div>
</div></div>
@endsection