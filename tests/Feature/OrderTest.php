<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\WebPushService;
use Tests\TestCase;

class OrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(WebPushService::class)->shouldReceive('notifyNewOrder')->andReturnNull();
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('checkout'));

        $response->assertRedirect(route('cart.index'));
    }

    public function test_checkout_shows_page_when_cart_has_items(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        app(CartService::class)->addProduct(Product::factory()->create(), 1);

        $response = $this->get(route('checkout'));

        $response->assertStatus(200);
    }

    public function test_store_creates_order_and_redirects(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        app(CartService::class)->addProduct(Product::factory()->create(['price' => 100, 'stock' => 5]), 2);

        $response = $this->post(route('orders.store'), [
            'name' => 'Test User',
            'phone' => '+79991234567',
            'email' => $user->email,
            'delivery_type' => 'delivery',
            'address' => 'Test Address 1',
            'payment' => 'cash',
            'comment' => null,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
    }

    public function test_store_validates_required_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        app(CartService::class)->addProduct(Product::factory()->create(), 1);

        $response = $this->post(route('orders.store'), []);

        $response->assertSessionHasErrors(['name', 'phone', 'email', 'delivery_type', 'payment']);
    }

    public function test_show_allows_owner_to_view_order(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->get(route('orders.show', $order));

        $response->assertStatus(200);
    }

    public function test_show_returns_403_for_other_users_order(): void
    {
        $order = Order::factory()->create(['user_id' => User::factory()->create()->id]);
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('orders.show', $order));

        $response->assertStatus(403);
    }

    public function test_my_orders_returns_200(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('orders.index'));

        $response->assertStatus(200);
    }

    public function test_store_redirects_with_error_when_cart_becomes_empty(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('orders.store'), [
            'name' => 'Test',
            'phone' => '123',
            'email' => $user->email,
            'delivery_type' => 'delivery',
            'address' => 'Addr',
            'payment' => 'cash',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error', 'Корзина пуста.');
    }

    public function test_show_allows_admin_to_view_any_order(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->create(['user_id' => User::factory()->create()->id]);
        $this->actingAs($admin);

        $response = $this->get(route('orders.show', $order));

        $response->assertStatus(200);
    }

    public function test_show_allows_view_via_session_key(): void
    {
        /** @var User $viewer */
        $viewer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => User::factory()->create()->id]);
        $this->actingAs($viewer);
        session()->put('order_view_' . $order->id, true);

        $response = $this->get(route('orders.show', $order));

        $response->assertStatus(200);
    }
}
