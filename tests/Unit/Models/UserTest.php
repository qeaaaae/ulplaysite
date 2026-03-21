<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_has_purchased_product_returns_true_when_purchased(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();

        $this->assertTrue($user->hasPurchasedProduct($product));
    }

    public function test_has_purchased_product_returns_false_when_not_purchased(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->assertFalse($user->hasPurchasedProduct($product));
    }

    public function test_has_purchased_service_returns_true_when_purchased(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forService($service)->create();

        $this->assertTrue($user->hasPurchasedService($service));
    }

    public function test_get_purchased_without_review_returns_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();

        $items = $user->getPurchasedWithoutReview();

        $this->assertCount(1, $items);
        $this->assertSame('product', $items->first()['type']);
        $this->assertSame($product->id, $items->first()['model']->id);
    }

    public function test_get_purchased_without_review_excludes_reviewed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();
        Review::create([
            'reviewable_type' => Product::class,
            'reviewable_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $items = $user->getPurchasedWithoutReview();

        $this->assertCount(0, $items);
    }
}
