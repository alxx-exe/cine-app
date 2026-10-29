@extends('layouts.app')
@section('title', $user->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ADMINISTRACIÓN</p><h1>{{ $user->exists ? 'Editar usuario' : 'Nuevo usuario' }}</h1></div><a class="text-link" href="{{ route('users.index') }}">← Volver a usuarios</a></div>
<form class="panel form-grid" method="post" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">@csrf @if($user->exists)@method('put')@endif
    <label>Nombre completo<input name="name" value="{{ old('name', $user->name) }}" maxlength="120" required></label>
    <label>Correo electrónico<input type="email" name="email" value="{{ old('email', $user->email) }}" required></label>
    <label>Teléfono<input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"></label>
    <label>Rol<select name="role_id" required>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((string)old('role_id', $user->role_id ?: $roles->firstWhere('name', 'cliente')?->id) === (string)$role->id)>{{ ucfirst($role->name) }}</option>@endforeach</select></label>
    <label>Fecha de nacimiento <span class="muted">(cliente)</span><input type="date" name="birth_date" value="{{ old('birth_date', $user->customerProfile?->birth_date?->format('Y-m-d')) }}"></label>
    <label>Carné estudiantil <span class="muted">(opcional)</span><input name="student_card" maxlength="40" value="{{ old('student_card', $user->customerProfile?->student_card) }}"></label>
    <label class="checkbox"><input type="checkbox" name="is_retired" value="1" @checked(old('is_retired', $user->customerProfile?->is_retired ?? false))> Cliente jubilado</label>
    <label>Cine asignado <span class="muted">(personal)</span><select name="cinema_id"><option value="">Sin asignar</option>@foreach($cinemas as $cinema)<option value="{{ $cinema->id }}" @selected((string)old('cinema_id', $user->employeeProfile?->cinema_id ?? $user->adminProfile?->cinema_id) === (string)$cinema->id)>{{ $cinema->name }}</option>@endforeach</select></label>
    <label>Cargo <span class="muted">(empleado)</span><input name="position" maxlength="40" value="{{ old('position', $user->employeeProfile?->position) }}"></label>
    <label>Fecha de vinculación <span class="muted">(empleado)</span><input type="date" name="hire_date" value="{{ old('hire_date', $user->employeeProfile?->hire_date?->format('Y-m-d')) }}"></label>
    <label>Contraseña {{ $user->exists ? '(déjala vacía para conservarla)' : '' }}<input type="password" name="password" autocomplete="new-password" {{ $user->exists ? '' : 'required' }}></label>
    <label>Confirma la contraseña<input type="password" name="password_confirmation" autocomplete="new-password" {{ $user->exists ? '' : 'required' }}></label>
    <div class="form-actions"><button class="button button-primary">Guardar usuario</button><a class="button button-quiet" href="{{ route('users.index') }}">Cancelar</a></div>
</form>
@endsection
