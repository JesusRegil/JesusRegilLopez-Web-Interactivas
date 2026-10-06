<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Torneos') · Torneos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .navbar { background: linear-gradient(90deg, #4338ca, #6d28d9); }
        .card { border: 0; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('torneos.index') }}"><i class="bi bi-trophy-fill"></i> Torneos</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('torneos.index') }}">Torneos disponibles</a></li>
                @auth
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.torneos.index') }}">Administrar torneos</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('mis-torneos') }}">Mis torneos</a></li>
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav align-items-md-center">
                @auth
                    <li class="nav-item text-white me-md-3">
                        <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                        <span class="badge {{ auth()->user()->isAdmin() ? 'bg-warning text-dark' : 'bg-light text-dark' }}">{{ auth()->user()->role }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="btn btn-sm btn-outline-light">Cerrar sesión</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-light" href="{{ route('register') }}">Registrarme</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container pb-5">
    @if(session('exito'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill"></i> {{ session('exito') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @yield('contenido')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>