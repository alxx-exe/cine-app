@extends('layouts.app')
@section('title', 'Películas')
@section('content')
<div class="page-heading"><div><p class="eyebrow">CATÁLOGO</p><h1>Películas</h1><p class="muted">Administra títulos, reparto y clasificación.</p></div><a class="button button-primary" href="{{ route('movies.create') }}">＋ Nueva película</a></div>
<div class="movie-grid">
@forelse($movies as $movie)<article class="movie-card">
    <div class="movie-poster movie-poster-large" aria-hidden="true">@if($movie->poster_url)<img src="{{ $movie->poster_url }}" alt="">@else<span>{{ strtoupper(substr($movie->title, 0, 1)) }}</span>@endif<small>{{ $movie->classification->code }}</small></div>
    <div class="movie-card-body"><div class="card-title-row"><h2>{{ $movie->title }}</h2><span class="pill">{{ $movie->is_active ? 'Activa' : 'Inactiva' }}</span></div>
    <p class="muted">{{ $movie->duration_minutes }} min · {{ $movie->genres->pluck('name')->join(', ') ?: 'Sin género' }}</p>
    <p>{{ \Illuminate\Support\Str::limit($movie->synopsis, 130) }}</p>
    <div class="actions"><a href="{{ route('movies.edit', $movie) }}">Editar película</a><form method="post" action="{{ route('movies.destroy', $movie) }}" onsubmit="return confirm('¿Eliminar esta película?')">@csrf @method('delete')<button class="link-danger">Eliminar</button></form></div></div>
</article>@empty<div class="empty-state"><h3>Aún no hay películas</h3><p>Agrega títulos al catálogo para programar funciones.</p></div>@endforelse
</div><div class="pagination">{{ $movies->links() }}</div>
@endsection
