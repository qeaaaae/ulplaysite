<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\WebPushService;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    public function test_create_throws_when_cart_is_empty(): void
    {
        Auth::login(User::factory()->create());

        $orderService = app(OrderService::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Корзина пуста.');

        $orderService->create(
            contactInfo: ['name' => 'Test', 'phone' => '123', 'email' => 'test@test.com'],
            deliveryInfo: ['address' => 'Address'],
            paymentInfo: ['method' => 'cash'],
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(WebPushService::class)->shouldReceive('notifyNewOrder')->andReturnNull();
    }

    public function test_create_creates_order_and_clears_cart(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $product = Product::factory()->create(['price' => 500, 'stock' => 5, 'discount_percent' => null]);
        app(CartService::class)->addProduct($product, 2);

        $orderService = app(OrderService::class);

        $order = $orderService->create(
            contactInfo: ['name' => 'Test', 'phone' => '123', 'email' => 'test@test.com'],
            deliveryInfo: ['address' => 'Address'],
            paymentInfo: ['method' => 'cash'],
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertStringStartsWith('UL-', $order->order_number);
        $this->assertSame('new', $order->status);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(1, $order->items->count());
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(1300.0, (float) $order->total);

        $this->assertSame(0, app(CartService::class)->count());
    }

    public function test_create_calculates_delivery_cost_zero_when_subtotal_above_3000(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $product = Product::factory()->create(['price' => 2000, 'stock' => 10, 'discount_percent' => null]);
        app(CartService::class)->addProduct($product, 2);

        $order = app(OrderService::class)->create(
            contactInfo: ['name' => 'Test', 'phone' => '123', 'email' => 'test@test.com'],
            deliveryInfo: ['address' => 'Address'],
            paymentInfo: ['method' => 'cash'],
        );

        $this->assertSame(4000.0, (float) $order->total);
        $this->assertSame(0, $order->delivery_info['delivery_cost']);
    }

    public function test_create_calculates_delivery_cost_300_when_subtotal_below_3000(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $product = Product::factory()->create(['price' => 100, 'stock' => 10, 'discount_percent' => null]);
        app(CartService::class)->addProduct($product, 1);

        $order = app(OrderService::class)->create(
            contactInfo: ['name' => 'Test', 'phone' => '123', 'email' => 'test@test.com'],
            deliveryInfo: ['address' => 'Address'],
            paymentInfo: ['method' => 'cash'],
        );

        $this->assertSame(400.0, (float) $order->total);
        $this->assertSame(300, $order->delivery_info['delivery_cost']);
    }
}
