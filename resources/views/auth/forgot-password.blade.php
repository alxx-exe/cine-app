@extends('layouts.app')
@section('title', 'Recuperar contraseña')
@section('content')
<section class="auth-wrap"><div class="auth-card">
    <p class="eyebrow">RECUPERACIÓN SEGURA</p><h1>Restablece tu acceso.</h1><p class="muted">Te enviaremos un enlace de un solo uso si el correo está registrado.</p>
    <form method="post" action="{{ route('password.email') }}" class="form-stack">@csrf
        <label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
        <button class="button button-primary button-wide">Enviar enlace de recuperación</button>
    </form>
    <p class="auth-bottom"><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
</div></section>
@endsection
