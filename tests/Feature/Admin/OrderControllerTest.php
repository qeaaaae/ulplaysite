<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();
        Order::factory()->count(2)->create();

        $response = $this->get(route('admin.orders.index'));

        $response->assertStatus(200);
    }

    public function test_show_returns_200(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create();

        $response = $this->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
    }

    public function test_update_status_changes_order_status(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create(['status' => 'new']);

        $response = $this->patch(route('admin.orders.update-status', $order), [
            'status' => 'processing',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');
        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_update_status_validates_status(): void
    {
        $this->actingAsAdmin();
        $order = Order::factory()->create();

        $response = $this->patch(route('admin.orders.update-status', $order), [
            'status' => 'invalid',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingAsAdmin();
        Order::factory()->create(['status' => 'new']);
        Order::factory()->create(['status' => 'completed']);

        $response = $this->get(route('admin.orders.index', ['status' => 'new']));

        $response->assertStatus(200);
    }
}
