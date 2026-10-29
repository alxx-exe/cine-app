@extends('layouts.app')
@section('title', $movie->exists ? 'Editar película' : 'Nueva película')
@section('content')
<div class="page-heading"><div><p class="eyebrow">CATÁLOGO</p><h1>{{ $movie->exists ? 'Editar película' : 'Nueva película' }}</h1></div><a class="text-link" href="{{ route('movies.index') }}">← Volver al catálogo</a></div>
<form class="panel form-grid" method="post" action="{{ $movie->exists ? route('movies.update', $movie) : route('movies.store') }}">@csrf @if($movie->exists)@method('put')@endif
    <label class="span-2">Título<input name="title" value="{{ old('title', $movie->title) }}" maxlength="180" required></label>
    <label>Clasificación<select name="classification_id" required><option value="">Selecciona…</option>@foreach($classifications as $classification)<option value="{{ $classification->id }}" @selected((string)old('classification_id', $movie->classification_id) === (string)$classification->id)>{{ $classification->code }} · {{ $classification->description }}</option>@endforeach</select></label>
    <label>Duración (minutos)<input type="number" name="duration_minutes" min="1" max="600" value="{{ old('duration_minutes', $movie->duration_minutes) }}" required></label>
    <label>Fecha de estreno<input type="date" name="release_date" value="{{ old('release_date', $movie->release_date?->format('Y-m-d')) }}"></label>
    <label>URL del afiche <span class="muted">(opcional)</span><input type="url" name="poster_url" value="{{ old('poster_url', $movie->poster_url) }}"></label>
    <label>Estado<select name="is_active"><option value="1" @selected((string)old('is_active', (int)($movie->is_active ?? true)) === '1')>Activa</option><option value="0" @selected((string)old('is_active', (int)($movie->is_active ?? true)) === '0')>Inactiva</option></select></label>
    <label class="span-2">Sinopsis<textarea name="synopsis" rows="4" maxlength="5000" required>{{ old('synopsis', $movie->synopsis) }}</textarea></label>
    <label>Géneros<select name="genres[]" multiple size="4">@foreach($genres as $genre)<option value="{{ $genre->id }}" @selected(in_array($genre->id, old('genres', $movie->genres->modelKeys()), false))>{{ $genre->name }}</option>@endforeach</select><small class="field-help">Ctrl o ⌘ + clic para seleccionar varios.</small></label>
    <label>Directores<select name="directors[]" multiple size="4">@foreach($directors as $director)<option value="{{ $director->id }}" @selected(in_array($director->id, old('directors', $movie->directors->modelKeys()), false))>{{ $director->name }}</option>@endforeach</select></label>
    <label class="span-2">Actores<select name="actors[]" multiple size="4">@foreach($actors as $actor)<option value="{{ $actor->id }}" @selected(in_array($actor->id, old('actors', $movie->actors->modelKeys()), false))>{{ $actor->name }}</option>@endforeach</select></label>
    <div class="form-actions"><button class="button button-primary">Guardar película</button><a class="button button-quiet" href="{{ route('movies.index') }}">Cancelar</a></div>
</form>
@endsection
