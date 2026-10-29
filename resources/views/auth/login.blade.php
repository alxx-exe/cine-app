@extends('layouts.app')
@section('title', 'Iniciar sesión')
@section('content')
<section class="auth-wrap"><div class="auth-card">
    <p class="eyebrow">BIENVENIDO DE NUEVO</p><h1>Entra al cine.</h1><p class="muted">Accede para descubrir funciones y gestionar tus reservas.</p>
    <form method="post" action="{{ route('login') }}" class="form-stack">@csrf
        <label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
        <label>Contraseña<input type="password" name="password" autocomplete="current-password" required></label>
        <label class="checkbox"><input type="checkbox" name="remember" value="1"> Mantener la sesión iniciada</label>
        <button class="button button-primary button-wide">Iniciar sesión</button>
    </form>
    <div class="auth-links"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a><span>¿Nuevo aquí? <a href="{{ route('register') }}">Crear cuenta</a></span></div>
</div></section>
@endsection
