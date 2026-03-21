<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    public function test_notifications_require_auth(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_notifications_returns_200(): void
    {
        $user = User::factory()->create();
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'ticket_reply',
            'title' => 'Test',
            'body' => 'Body',
            'read_at' => null,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('notifications.index'));

        $response->assertStatus(200);
    }
}
