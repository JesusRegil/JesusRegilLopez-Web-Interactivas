<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Recetario casero') | Recetario casero</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-amber-50 text-stone-800">
    <header class="border-b border-amber-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <a href="{{ route('recetas.index') }}" class="text-xl font-bold text-amber-700">Recetario casero</a>

            @auth
                <nav class="flex flex-wrap items-center gap-4 text-sm">
                    <a href="{{ route('recetas.index') }}" class="font-medium hover:text-amber-700">Mis recetas</a>
                    <a href="{{ route('recetas.create') }}" class="font-medium hover:text-amber-700">Nueva receta</a>
                    <span class="text-stone-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded border border-stone-300 px-3 py-1 hover:bg-stone-100">Cerrar sesión</button>
                    </form>
                </nav>
            @endauth
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6">
        @if (session('exito'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800" role="status">
                {{ session('exito') }}
            </div>
        @endif

        @yield('contenido')
    </main>
</body>
</html>