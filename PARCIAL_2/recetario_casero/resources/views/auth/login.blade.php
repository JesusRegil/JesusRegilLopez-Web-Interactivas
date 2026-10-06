@extends('layouts.app')

@section('titulo', 'Iniciar sesión')

@section('contenido')
    <div class="mx-auto max-w-md rounded border border-stone-200 bg-white p-6 shadow-sm">
        <h1 class="mb-4 text-2xl font-bold">Iniciar sesión</h1>

        <form method="POST" action="{{ route('login') }}" novalidate class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email"
                       class="mt-1 w-full rounded border px-3 py-2 @error('email') border-red-500 @else border-stone-300 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium">Contraseña</label>
                <input type="password" id="password" name="password" autocomplete="current-password"
                       class="mt-1 w-full rounded border px-3 py-2 @error('password') border-red-500 @else border-stone-300 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700">Entrar</button>
        </form>

        <p class="mt-4 text-sm text-stone-600">
            ¿Todavía no tienes cuenta?
            <a href="{{ route('registro') }}" class="font-medium text-amber-700 hover:underline">Regístrate</a>
        </p>
    </div>
@endsection