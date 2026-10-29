@extends('layouts.app')
@section('title', 'Funciones')
@section('content')
<div class="page-heading"><div><p class="eyebrow">PROGRAMACIÓN</p><h1>Funciones</h1><p class="muted">Consulta y organiza las próximas proyecciones.</p></div><a class="button button-primary" href="{{ route('screenings.create') }}">＋ Programar función</a></div>
<div class="table-wrap"><table><thead><tr><th>Película</th><th>Fecha y hora</th><th>Cine / sala</th><th>Formato</th><th>Tarifa</th><th></th></tr></thead><tbody>
@forelse($screenings as $screening)<tr><td class="table-primary">{{ $screening->movie->title }}</td><td>{{ $screening->starts_at->translatedFormat('d M Y · H:i') }}</td><td>{{ $screening->screen->cinema->name }}<small class="table-sub">{{ $screening->screen->name }}</small></td><td><span class="pill">{{ $screening->format }}</span></td><td>${{ number_format((float)$screening->ticketPrice(), 0, ',', '.') }}</td><td class="actions"><a href="{{ route('screenings.edit', $screening) }}">Editar</a><form method="post" action="{{ route('screenings.destroy', $screening) }}" onsubmit="return confirm('¿Eliminar esta función?')">@csrf @method('delete')<button class="link-danger">Eliminar</button></form></td></tr>
@empty<tr><td colspan="6" class="empty-cell">No hay próximas funciones programadas.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $screenings->links() }}</div>
@endsection
