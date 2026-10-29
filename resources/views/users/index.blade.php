@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ADMINISTRACIÓN</p><h1>Usuarios</h1><p class="muted">Gestiona las cuentas y sus permisos.</p></div><a class="button button-primary" href="{{ route('users.create') }}">＋ Nuevo usuario</a></div>
<div class="table-wrap"><table><thead><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th></th></tr></thead><tbody>
@forelse($users as $user)<tr><td class="table-primary">{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->phone ?: '—' }}</td><td><span class="pill">{{ ucfirst($user->role->name) }}</span></td><td class="actions"><a href="{{ route('users.edit', $user) }}">Editar</a><form method="post" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar esta cuenta?')">@csrf @method('delete')<button class="link-danger">Eliminar</button></form></td></tr>
@empty<tr><td colspan="5" class="empty-cell">No hay usuarios registrados.</td></tr>@endforelse
</tbody></table></div>
<div class="pagination">{{ $users->links() }}</div>
@endsection
