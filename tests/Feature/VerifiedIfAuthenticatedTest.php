<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Tests\TestCase;

class VerifiedIfAuthenticatedTest extends TestCase
{
    public function test_unverified_user_cannot_post_to_cart(): void
    {
        $user = User::factory()->unverified()->create();
        $product = Product::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('cart.add-product', $product), ['quantity' => 1]);

        $response->assertStatus(403);
    }

    public function test_unverified_user_cannot_post_order(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        app(CartService::class)->addProduct(Product::factory()->create(), 1);

        $response = $this->post(route('orders.store'), [
            'name' => 'Test',
            'phone' => '123',
            'email' => $user->email,
            'address' => 'Addr',
            'payment' => 'cash',
        ]);

        $response->assertStatus(403);
    }

    public function test_unverified_user_can_get_cart_page(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $response = $this->get(route('cart.index'));

        $response->assertStatus(200);
    }
}
