@extends('layouts.app')

@section('titulo', 'Crear cuenta')

@section('contenido')
    <div class="mx-auto max-w-md rounded border border-stone-200 bg-white p-6 shadow-sm">
        <h1 class="mb-4 text-2xl font-bold">Crear cuenta</h1>

        <form method="POST" action="{{ route('registro') }}" novalidate class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium">Nombre</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name"
                       class="mt-1 w-full rounded border px-3 py-2 @error('name') border-red-500 @else border-stone-300 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

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
                <input type="password" id="password" name="password" autocomplete="new-password"
                       class="mt-1 w-full rounded border px-3 py-2 @error('password') border-red-500 @else border-stone-300 @enderror">
                <p class="mt-1 text-sm text-stone-500">Mínimo 8 caracteres.</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium">Confirmar contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                       class="mt-1 w-full rounded border border-stone-300 px-3 py-2">
            </div>

            <button type="submit" class="w-full rounded bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700">Crear cuenta</button>
        </form>

        <p class="mt-4 text-sm text-stone-600">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="font-medium text-amber-700 hover:underline">Inicia sesión</a>
        </p>
    </div>
@endsection