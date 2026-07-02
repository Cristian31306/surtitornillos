<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_their_own_password_successfully(): void
    {
        // 1. Crear un usuario con una contraseña conocida
        $user = User::create([
            'username' => 'testuser',
            'name'     => 'Test User',
            'password' => Hash::make('oldpassword'),
            'role'     => 'colaborador',
        ]);

        // 2. Simular que el usuario está autenticado
        $this->actingAs($user);

        // 3. Enviar solicitud PUT para cambiar contraseña
        $response = $this->put(route('profile.password.update'), [
            'current_password'      => 'oldpassword',
            'password'              => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        // 4. Verificar redirección exitosa (back)
        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Contraseña actualizada con éxito.');

        // 5. Verificar que la contraseña cambió en la base de datos
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    public function test_user_cannot_change_password_if_current_password_is_incorrect(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'name'     => 'Test User',
            'password' => Hash::make('oldpassword'),
            'role'     => 'colaborador',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('profile.password.update'), [
            'current_password'      => 'incorrectpassword',
            'password'              => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['current_password']);
        
        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword', $user->password));
    }

    public function test_user_cannot_change_password_if_confirmation_does_not_match(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'name'     => 'Test User',
            'password' => Hash::make('oldpassword'),
            'role'     => 'colaborador',
        ]);

        $this->actingAs($user);

        $response = $this->put(route('profile.password.update'), [
            'current_password'      => 'oldpassword',
            'password'              => 'newpassword',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password']);

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword', $user->password));
    }

    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $response = $this->put(route('profile.password.update'), [
            'current_password'      => 'oldpassword',
            'password'              => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertRedirect('/login');
    }
}
