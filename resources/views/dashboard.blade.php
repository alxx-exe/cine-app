@extends('layouts.app')
@section('title', 'Cartelera')
@section('content')
<section class="hero">
    <div><p class="eyebrow">CARTELERA · {{ now()->translatedFormat('l, d \d\e F') }}</p><h1>Una buena historia<br>se vive en pantalla.</h1><p>Elige tu función, encuentra tus asientos y disfruta el momento.</p><a class="button button-primary" href="#funciones">Ver próximas funciones <span aria-hidden="true">→</span></a></div>
    <div class="hero-art" aria-hidden="true"><div class="reel reel-one"></div><div class="reel reel-two"></div><div class="hero-ticket">CINE<br><strong>HOY</strong></div></div>
</section>
<section class="stats-grid" aria-label="Resumen">
    <article class="stat-card"><span>Películas en cartelera</span><strong>{{ $movieCount }}</strong><small>Historias para descubrir</small></article>
    <article class="stat-card"><span>{{ auth()->user()->hasRole('cliente') ? 'Mis reservas' : 'Reservas registradas' }}</span><strong>{{ $reservationCount }}</strong><small>Entradas confirmadas</small></article>
    @if($userCount !== null)<article class="stat-card"><span>Usuarios</span><strong>{{ $userCount }}</strong><small>En la plataforma</small></article>@endif
</section>
<section id="funciones" class="section-block">
    <div class="section-heading"><div><p class="eyebrow">PRÓXIMAMENTE</p><h2>Funciones destacadas</h2></div>
        @if(auth()->user()->hasRole('administrador', 'empleado'))<a class="text-link" href="{{ route('screenings.index') }}">Administrar funciones →</a>@endif
    </div>
    @if($screenings->isEmpty())<div class="empty-state"><h3>Pronto habrá más funciones</h3><p>Vuelve a consultar la cartelera más adelante.</p></div>
    @else
    <div class="screening-grid">
        @foreach($screenings as $screening)
            <article class="screening-card">
                <div class="movie-poster" aria-hidden="true"><span>{{ strtoupper(substr($screening->movie->title, 0, 1)) }}</span><small>{{ $screening->movie->classification->code }}</small></div>
                <div class="screening-content"><div class="card-title-row"><h3>{{ $screening->movie->title }}</h3><span class="pill">{{ $screening->format }}</span></div>
                    <p class="muted">{{ $screening->movie->duration_minutes }} min · {{ $screening->movie->classification->description }}</p>
                    <div class="screening-meta"><span>◷ {{ $screening->starts_at->translatedFormat('D d M · H:i') }}</span><span>{{ $screening->screen->cinema->name }} · {{ $screening->screen->name }}</span></div>
                    <div class="card-footer"><strong>${{ number_format((float) $screening->ticketPrice(), 0, ',', '.') }}</strong>
                        @if(auth()->user()->hasRole('cliente'))<a class="button button-primary button-small" href="{{ route('reservations.seats', $screening) }}">Elegir asientos</a>@endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    @endif
</section>
@endsection
