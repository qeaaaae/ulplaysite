<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_get_redirects_guest_to_home(): void
    {
        $response = $this->get(route('login'));

        $response->assertRedirect(route('home'));
    }

    public function test_register_get_redirects_guest_to_home(): void
    {
        $response = $this->get(route('register'));

        $response->assertRedirect(route('home'));
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_login_fails_for_blocked_user(): void
    {
        User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => Hash::make('password'),
            'is_blocked' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'blocked@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_register_creates_user_and_redirects(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertAuthenticated();
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_logout_clears_session(): void
    {
        $this->actingAsUser();

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_login_returns_json_when_expects_json(): void
    {
        $user = User::factory()->create([
            'email' => 'json@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson(route('login'), [
            'email' => 'json@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['redirect']);
    }

    public function test_login_returns_json_errors_when_invalid(): void
    {
        $response = $this->postJson(route('login'), [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_register_returns_json_when_expects_json(): void
    {
        $response = $this->postJson(route('register'), [
            'name' => 'Json User',
            'email' => 'jsonuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['redirect']);
    }
}
