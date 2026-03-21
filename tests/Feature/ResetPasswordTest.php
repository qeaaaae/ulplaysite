<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    public function test_reset_form_returns_200(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'fake-token']));

        $response->assertStatus(200);
    }

    public function test_reset_validates_required_fields(): void
    {
        $response = $this->post(route('password.update'), []);

        $response->assertSessionHasErrors(['token', 'email', 'password']);
    }

    public function test_reset_succeeds_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('status');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
