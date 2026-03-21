<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    public function test_store_product_review_requires_purchase(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('reviews.store.product', $product), [
            'rating' => 5,
            'body' => 'Great product',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_store_product_review_succeeds_when_purchased(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();
        $this->actingAs($user);

        $response = $this->post(route('reviews.store.product', $product), [
            'rating' => 5,
            'body' => 'Great product',
        ]);

        $response->assertRedirect(route('products.show', $product));
        $this->assertDatabaseHas('reviews', [
            'reviewable_type' => Product::class,
            'reviewable_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_store_product_review_fails_when_already_reviewed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();
        Review::create([
            'reviewable_type' => Product::class,
            'reviewable_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);
        $this->actingAs($user);

        $response = $this->post(route('reviews.store.product', $product), [
            'rating' => 5,
            'body' => 'Another review',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_store_service_review_succeeds_when_purchased(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forService($service)->create();
        $this->actingAs($user);

        $response = $this->post(route('reviews.store.service', $service), [
            'rating' => 5,
            'body' => 'Great service',
        ]);

        $response->assertRedirect(route('services.show', $service));
        $this->assertDatabaseHas('reviews', [
            'reviewable_type' => Service::class,
            'reviewable_id' => $service->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_store_product_review_returns_json_when_wants_json(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();
        $this->actingAs($user);

        $response = $this->postJson(route('reviews.store.product', $product), [
            'rating' => 5,
            'body' => 'Great',
        ]);

        $response->assertOk();
        $response->assertJson(['result' => true]);
        $response->assertJsonStructure(['html']);
    }

    public function test_store_product_review_returns_json_errors_when_not_purchased(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('reviews.store.product', $product), [
            'rating' => 5,
            'body' => 'Great',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('rating');
    }

    public function test_store_product_review_validates_rating(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();
        $this->actingAs($user);

        $response = $this->post(route('reviews.store.product', $product), [
            'rating' => 10,
            'body' => 'Great',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_store_service_review_returns_json_when_wants_json(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->forOrder($order)->forService($service)->create();
        $this->actingAs($user);

        $response = $this->postJson(route('reviews.store.service', $service), [
            'rating' => 5,
            'body' => 'Great',
        ]);

        $response->assertOk();
        $response->assertJson(['result' => true]);
    }
}
