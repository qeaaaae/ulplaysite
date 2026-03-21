<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\WebPushService;
use Tests\TestCase;

class PushSubscriptionControllerTest extends TestCase
{
    public function test_store_requires_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->postJson(route('admin.push-subscription.store'), [
            'endpoint' => 'https://example.com/push',
            'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
        ]);

        $response->assertStatus(403);
    }

    public function test_store_succeeds_for_admin(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson(route('admin.push-subscription.store'), [
            'endpoint' => 'https://example.com/push',
            'keys' => ['p256dh' => 'key', 'auth' => 'auth'],
        ]);

        $response->assertJson(['success' => true]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson(route('admin.push-subscription.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['endpoint', 'keys']);
    }

    public function test_test_returns_json_for_admin(): void
    {
        $this->mock(WebPushService::class)
            ->shouldReceive('sendTestToUser')
            ->andReturn(true);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $response = $this->postJson(route('admin.push-subscription.test'));

        $response->assertJson(['success' => true]);
    }
}
