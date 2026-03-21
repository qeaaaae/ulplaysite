<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_profile_requires_auth(): void
    {
        $response = $this->get(route('profile'));

        $response->assertRedirect(route('login'));
    }

    public function test_profile_returns_200_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('profile'));

        $response->assertStatus(200);
    }

    public function test_profile_update_succeeds(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'phone' => '+79991234567',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('message');
        $this->assertSame('Updated Name', $user->fresh()->name);
    }

    public function test_profile_update_validates_email_unique(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);

        $response = $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $other->email,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_profile_update_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->patch(route('profile.update'), []);

        $response->assertSessionHasErrors(['name', 'email']);
    }
}
