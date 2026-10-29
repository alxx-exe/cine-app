@extends('layouts.app')
@section('title', 'Crear cuenta')
@section('content')
<section class="auth-wrap"><div class="auth-card">
    <p class="eyebrow">TU BUTACA TE ESPERA</p><h1>Crea tu cuenta.</h1><p class="muted">Regístrate como cliente para reservar tus asientos.</p>
    <form method="post" action="{{ route('register') }}" class="form-stack">@csrf
        <label>Nombre completo<input name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required></label>
        <label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label>
        <label>Teléfono <span class="muted">(opcional)</span><input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"></label>
        <label>Contraseña <span class="muted">(10 caracteres, mayúscula, minúscula y número)</span><input type="password" name="password" autocomplete="new-password" required></label>
        <label>Confirma la contraseña<input type="password" name="password_confirmation" autocomplete="new-password" required></label>
        <button class="button button-primary button-wide">Crear cuenta</button>
    </form>
    <p class="auth-bottom">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
</div></section>
@endsection
