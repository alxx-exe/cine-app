@extends('layouts.app')
@section('title', 'Nueva contraseña')
@section('content')
<section class="auth-wrap"><div class="auth-card">
    <p class="eyebrow">RECUPERACIÓN SEGURA</p><h1>Elige una contraseña nueva.</h1>
    <form method="post" action="{{ route('password.store') }}" class="form-stack">@csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <label>Correo electrónico<input type="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="email" required></label>
        <label>Nueva contraseña<input type="password" name="password" autocomplete="new-password" required></label>
        <label>Confirma la contraseña<input type="password" name="password_confirmation" autocomplete="new-password" required></label>
        <button class="button button-primary button-wide">Guardar contraseña</button>
    </form>
</div></section>
@endsection
