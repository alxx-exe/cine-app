@extends('layouts.app')
@section('title', $screening->exists ? 'Editar función' : 'Programar función')
@section('content')
<div class="page-heading"><div><p class="eyebrow">PROGRAMACIÓN</p><h1>{{ $screening->exists ? 'Editar función' : 'Programar función' }}</h1></div><a class="text-link" href="{{ route('screenings.index') }}">← Volver a funciones</a></div>
<form class="panel form-grid" method="post" action="{{ $screening->exists ? route('screenings.update', $screening) : route('screenings.store') }}">@csrf @if($screening->exists)@method('put')@endif
    <label class="span-2">Película<select name="movie_id" required><option value="">Selecciona…</option>@foreach($movies as $movie)<option value="{{ $movie->id }}" @selected((string)old('movie_id', $screening->movie_id) === (string)$movie->id)>{{ $movie->title }}</option>@endforeach</select></label>
    <label>Cine<select name="cinema_id" required><option value="">Selecciona…</option>@foreach($cinemas as $cinema)<option value="{{ $cinema->id }}" @selected((string)old('cinema_id', $screening->screen?->cinema_id) === (string)$cinema->id)>{{ $cinema->name }} · {{ $cinema->city }}</option>@endforeach</select></label>
    <label>Sala<select name="screen_id" required><option value="">Selecciona…</option>@foreach($cinemas as $cinema)<optgroup label="{{ $cinema->name }}">@foreach($cinema->screens as $screen)<option value="{{ $screen->id }}" @selected((string)old('screen_id', $screening->screen_id) === (string)$screen->id)>{{ $screen->name }} · {{ $screen->capacity }} puestos</option>@endforeach</optgroup>@endforeach</select></label>
    <label>Fecha y hora<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $screening->starts_at?->format('Y-m-d\TH:i')) }}" required></label>
    <label>Tarifa<select name="tariff_id" required><option value="">Selecciona…</option>@foreach($tariffs as $tariff)<option value="{{ $tariff->id }}" @selected((string)old('tariff_id', $screening->tariff_id) === (string)$tariff->id)>{{ $tariff->name }}</option>@endforeach</select><small class="field-help">El precio se determina según el cine y el día de la función.</small></label>
    <label>Formato<select name="format"><option @selected(old('format', $screening->format ?: '2D') === '2D')>2D</option><option @selected(old('format', $screening->format) === '3D')>3D</option><option @selected(old('format', $screening->format) === 'IMAX')>IMAX</option></select></label>
    <label>Idioma<input name="language" maxlength="30" value="{{ old('language', $screening->language ?: 'Español') }}" required></label>
    <div class="form-actions"><button class="button button-primary">Guardar función</button><a class="button button-quiet" href="{{ route('screenings.index') }}">Cancelar</a></div>
</form>
@endsection
