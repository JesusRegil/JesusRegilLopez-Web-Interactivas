@extends('layouts.app')
@section('titulo', 'Iniciar sesión')

@section('contenido')
<div class="row justify-content-center"><div class="col-md-5">
    <div class="card"><div class="card-body">
        <h1 class="h4 mb-3">Iniciar sesión</h1>
        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary w-100">Entrar</button>
        </form>
        <p class="mt-3 mb-0 small">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
    </div></div>
</div></div>
@endsection