<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\AdminProfile;
use App\Models\CustomerProfile;
use App\Models\EmployeeProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreateAdminCommand extends Command
{
    protected $signature = 'cine:admin';

    protected $description = 'Crear o promover una cuenta de administrador solicitando la contrasena de forma segura';

    public function handle(): int
    {
        $name = $this->ask('Nombre del administrador');
        $email = strtolower((string) $this->ask('Correo electronico'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Escribe un correo electronico valido.');
            return self::FAILURE;
        }

        $password = $this->secret('Contrasena (minimo 10 caracteres, mayuscula, minuscula y numero)');
        $confirmation = $this->secret('Confirma la contrasena');
        if (! is_string($password) || strlen($password) < 10 || ! hash_equals($password, (string) $confirmation)
            || ! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password) || ! preg_match('/[0-9]/', $password)) {
            $this->error('La contrasena no coincide o no cumple la politica de seguridad.');
            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'role_id' => Role::where('name', 'administrador')->value('id'),
                'name' => (string) $name,
                'password' => Hash::make($password),
            ]
        );
        CustomerProfile::where('user_id', $user->id)->delete();
        EmployeeProfile::where('user_id', $user->id)->delete();
        AdminProfile::firstOrCreate(['user_id' => $user->id]);
        $this->info('Cuenta de administrador creada o actualizada.');

        return self::SUCCESS;
    }
}
