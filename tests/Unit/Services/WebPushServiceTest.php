<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\User;
use App\Services\WebPushService;
use Tests\TestCase;

class WebPushServiceTest extends TestCase
{
    public function test_send_test_to_user_returns_false_when_no_subscriptions(): void
    {
        $user = User::factory()->create();
        $service = app(WebPushService::class);

        $result = $service->sendTestToUser($user->id);

        $this->assertFalse($result);
    }

    public function test_notify_new_order_does_not_throw_when_no_admin_subscriptions(): void
    {
        $order = Order::factory()->create();
        $service = app(WebPushService::class);

        $service->notifyNewOrder($order);

        $this->assertTrue(true);
    }
}
