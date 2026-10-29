@extends('layouts.app')
@section('title', 'Elegir asientos')
@section('content')
<div class="page-heading"><div><p class="eyebrow">RESERVA DE ENTRADAS</p><h1>Elige tus asientos</h1><p class="muted">{{ $screening->movie->title }} · {{ $screening->starts_at->translatedFormat('l d M · H:i') }}</p></div><a class="text-link" href="{{ route('dashboard') }}">← Volver a cartelera</a></div>
<div class="booking-layout">
    <section class="panel seat-panel">
        <div class="seat-legend"><span><i class="seat-dot available"></i>Disponible</span><span><i class="seat-dot selected"></i>Tu selección</span><span><i class="seat-dot occupied"></i>Ocupado</span></div>
        <div class="screen-label"><span></span>PANTALLA<span></span></div>
        <form method="post" action="{{ route('reservations.store', $screening) }}" id="seat-form">@csrf
            <div class="seat-map">
                @foreach($screening->screen->seats->groupBy('row_label') as $row => $seats)
                    <div class="seat-row"><span class="row-label">{{ $row }}</span>
                        @foreach($seats->sortBy('number') as $seat)
                            @php($isTaken = in_array($seat->id, $taken, true))
                            <label class="seat-label {{ $isTaken ? 'seat-taken' : '' }}" title="Fila {{ $seat->row_label }}, asiento {{ $seat->number }}">
                                <input type="checkbox" name="seat_ids[]" value="{{ $seat->id }}" @disabled($isTaken)>
                                <span>{{ $seat->number }}</span>
                            </label>
                        @endforeach
                        <span class="row-label">{{ $row }}</span>
                    </div>
                @endforeach
            </div>
            <div class="booking-summary">            <div><span class="muted">Precio por entrada</span><strong>${{ number_format((float)$screening->ticketPrice(), 0, ',', '.') }}</strong></div><div><span class="muted">Máximo por reserva</span><strong>8 asientos</strong></div>
                <button class="button button-primary" type="submit">Confirmar reserva</button></div>
            <p class="field-help">La reserva se confirma al completar el pago en taquilla. La disponibilidad se verifica nuevamente al confirmar.</p>
        </form>
    </section>
    <aside class="booking-info"><span class="pill">{{ $screening->movie->classification->code }}</span><h2>{{ $screening->movie->title }}</h2><p>{{ $screening->screen->cinema->name }} · {{ $screening->screen->name }}</p><p>{{ $screening->starts_at->translatedFormat('l d \d\e F · H:i') }} · {{ $screening->format }} · {{ $screening->language }}</p><hr><strong>Precio: ${{ number_format((float)$screening->ticketPrice(), 0, ',', '.') }}</strong><p class="muted">{{ $screening->movie->classification->description }}</p></aside>
</div>
@endsection
