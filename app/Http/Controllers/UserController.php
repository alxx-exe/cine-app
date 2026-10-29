<?php

namespace App\Http\Controllers;

use App\Models\AdminProfile;
use App\Models\Cinema;
use App\Models\CustomerProfile;
use App\Models\EmployeeProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['users' => User::with('role')->orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('users.form', $this->formData(new User()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data): void {
            $user = User::create([
                'role_id' => $data['role_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);
            $this->syncProfile($user, $data);
        });

        return redirect()->route('users.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        $user->load(['role', 'customerProfile', 'employeeProfile', 'adminProfile']);

        return view('users.form', $this->formData($user));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $newRole = Role::findOrFail($data['role_id'])->name;
        if ($user->reservations()->exists() && $newRole !== 'cliente') {
            return back()->withErrors(['role_id' => 'No se puede cambiar el rol de un cliente con reservas registradas.'])->withInput();
        }
        DB::transaction(function () use ($data, $user): void {
            $attributes = [
                'role_id' => $data['role_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];
            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
            }
            $user->update($attributes);
            $this->syncProfile($user, $data);
        });

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => 'No puedes eliminar tu propia cuenta desde esta pantalla.']);
        }
        if ($user->reservations()->exists()) {
            return back()->withErrors(['user' => 'No se puede eliminar un usuario que tiene reservas registradas.']);
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $isCreate = $user === null;

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => [$isCreate ? 'required' : 'nullable', 'confirmed', Password::min(10)->mixedCase()->numbers()],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'student_card' => ['nullable', 'string', 'max:40', Rule::unique('customer_profiles', 'student_card')->ignore($user?->id, 'user_id')],
            'is_retired' => ['sometimes', 'boolean'],
            'cinema_id' => ['nullable', 'exists:cinemas,id'],
            'position' => ['nullable', 'string', 'max:40'],
            'hire_date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);
    }

    private function syncProfile(User $user, array $data): void
    {
        $role = Role::findOrFail($data['role_id'])->name;
        if ($role === 'cliente') {
            EmployeeProfile::where('user_id', $user->id)->delete();
            AdminProfile::where('user_id', $user->id)->delete();
            CustomerProfile::updateOrCreate(['user_id' => $user->id], [
                'birth_date' => $data['birth_date'] ?? null,
                'student_card' => $data['student_card'] ?? null,
                'is_retired' => $data['is_retired'] ?? false,
            ]);
        } elseif ($role === 'empleado') {
            CustomerProfile::where('user_id', $user->id)->delete();
            AdminProfile::where('user_id', $user->id)->delete();
            EmployeeProfile::updateOrCreate(['user_id' => $user->id], [
                'cinema_id' => $data['cinema_id'] ?? null,
                'position' => $data['position'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
            ]);
        } else {
            CustomerProfile::where('user_id', $user->id)->delete();
            EmployeeProfile::where('user_id', $user->id)->delete();
            AdminProfile::updateOrCreate(['user_id' => $user->id], [
                'cinema_id' => $data['cinema_id'] ?? null,
            ]);
        }
    }

    private function formData(User $user): array
    {
        return [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
            'cinemas' => Cinema::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
