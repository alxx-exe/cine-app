<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'administrador']);
        Role::firstOrCreate(['name' => 'empleado']);
        Role::firstOrCreate(['name' => 'cliente']);
    }

    public function test_registration_always_creates_a_customer_and_hashes_the_password(): void
    {
        $password = Str::random(16).'aA1';
        $response = $this->post('/register', [
            'name' => 'Cliente de prueba',
            'email' => 'cliente@example.test',
            'password' => $password,
            'password_confirmation' => $password,
            'role' => 'administrador',
        ]);

        $response->assertRedirect('/dashboard');
        $user = User::where('email', 'cliente@example.test')->firstOrFail();
        $this->assertSame('cliente', $user->roleName());
        $this->assertDatabaseHas('customer_profiles', ['user_id' => $user->id]);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNotSame($password, $user->password);
    }
}
