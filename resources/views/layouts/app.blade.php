<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CineApp') · CineApp</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('dashboard') }}"><span class="brand-icon">C</span> cine<span>app</span></a>
    @auth
        <nav class="nav-links" aria-label="Navegación principal">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <a href="{{ route('reservations.index') }}">Reservas</a>
            @if(auth()->user()->hasRole('administrador', 'empleado'))
                <a href="{{ route('screenings.index') }}">Funciones</a>
            @endif
            @if(auth()->user()->hasRole('administrador'))
                <a href="{{ route('movies.index') }}">Películas</a>
                <a href="{{ route('users.index') }}">Usuarios</a>
            @endif
        </nav>
        <div class="account">
            <span class="account-name">{{ auth()->user()->name }} <small>{{ ucfirst(auth()->user()->roleName()) }}</small></span>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="button button-quiet" type="submit">Salir</button></form>
        </div>
    @else
        <nav class="nav-links"><a href="{{ route('login') }}">Iniciar sesión</a><a class="button button-primary button-small" href="{{ route('register') }}">Crear cuenta</a></nav>
    @endauth
</header>
<main class="page-shell">
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-error" role="alert"><strong>Revisa estos datos:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
<footer class="footer">CineApp <span>·</span> Tu próxima historia empieza aquí.</footer>
</body>
</html>
