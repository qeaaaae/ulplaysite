<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Models\User;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    public function test_guest_cannot_access_admin(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(200);
    }
}
