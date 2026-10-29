@extends('layouts.app')
@section('title', 'Reservas')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ENTRADAS</p><h1>{{ auth()->user()->hasRole('cliente') ? 'Mis reservas' : 'Reservas' }}</h1><p class="muted">Consulta los detalles y códigos de confirmación.</p></div><a class="button button-primary" href="{{ route('dashboard') }}">Ver cartelera</a></div>
<div class="reservation-list">
@forelse($reservations as $reservation)
    @php($screening = $reservation->tickets->first()?->screening)
    <article class="reservation-card"><div class="reservation-date"><span>{{ $screening?->starts_at->format('d') }}</span><small>{{ strtoupper($screening?->starts_at->translatedFormat('M') ?? '') }}</small></div>
        <div class="reservation-details"><div class="card-title-row"><h2>{{ $screening?->movie->title ?? 'Reserva' }}</h2><span class="pill">{{ ucfirst($reservation->status) }}</span></div>
            @if(!auth()->user()->hasRole('cliente'))<p class="muted">Cliente: {{ $reservation->user->name }} · {{ $reservation->user->email }}</p>@endif
            <p>{{ $screening?->screen?->cinema?->name }} · {{ $screening?->starts_at->translatedFormat('l H:i') }}</p>
            <p class="muted">Asientos: {{ $reservation->tickets->map(fn ($ticket) => $ticket->seat->row_label.$ticket->seat->number)->join(', ') }}</p>
        </div><div class="reservation-price"><small>Código {{ $reservation->confirmation_code }}</small><strong>${{ number_format($reservation->tickets->sum(fn ($ticket) => (float) $ticket->unit_price), 0, ',', '.') }}</strong></div>
    </article>
@empty<div class="empty-state"><h3>Aún no hay reservas</h3><p>Explora la cartelera para elegir tu próxima película.</p><a class="button button-primary" href="{{ route('dashboard') }}">Explorar funciones</a></div>@endforelse
</div><div class="pagination">{{ $reservations->links() }}</div>
@endsection
