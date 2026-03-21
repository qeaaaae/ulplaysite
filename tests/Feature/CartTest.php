<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use Tests\TestCase;

class CartTest extends TestCase
{
    public function test_add_product_succeeds_for_authenticated_user(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->post(route('cart.add-product', $product), ['quantity' => 2]);

        $response->assertRedirect();
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_add_product_succeeds_for_guest(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->post(route('cart.add-product', $product), ['quantity' => 1]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'user_id' => null,
        ]);
    }

    public function test_add_product_respects_stock_validation(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $product = Product::factory()->create(['stock' => 3]);

        $response = $this->post(route('cart.add-product', $product), ['quantity' => 10]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_add_service_succeeds(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $service = Service::factory()->create();

        $response = $this->post(route('cart.add-service', $service), ['quantity' => 1]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_add_service_succeeds_for_guest(): void
    {
        $service = Service::factory()->create();

        $response = $this->post(route('cart.add-service', $service), ['quantity' => 1]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'service_id' => $service->id,
            'user_id' => null,
        ]);
    }

    public function test_update_cart_item_quantity(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = CartItem::factory()->forProduct($product, 2)->forUser($user)->create([
            'session_id' => session()->getId(),
        ]);

        $response = $this->patch(route('cart.update', $item), ['quantity' => 5]);

        $response->assertRedirect(route('cart.index'));
        $this->assertSame(5, $item->fresh()->quantity);
    }

    public function test_remove_cart_item(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = CartItem::factory()->forProduct($product, 1)->forUser($user)->create([
            'session_id' => session()->getId(),
        ]);

        $response = $this->delete(route('cart.remove', $item));

        $response->assertRedirect(route('cart.index'));
        $this->assertNull(CartItem::find($item->id));
    }

    public function test_clear_cart(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        CartItem::factory()->forProduct(Product::factory()->create(), 1)->forUser($user)->create([
            'session_id' => session()->getId(),
        ]);

        $response = $this->post(route('cart.clear'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('message');
        $this->assertSame(0, CartItem::where('user_id', $user->id)->count());
    }

    public function test_cart_index_returns_200_for_authenticated_user(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $response = $this->get(route('cart.index'));

        $response->assertStatus(200);
    }

    public function test_cart_index_returns_200_for_guest(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertStatus(200);
    }

    public function test_add_product_returns_json_when_wants_json(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->postJson(route('cart.add-product', $product), ['quantity' => 1]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['cartCount']);
    }

    public function test_update_returns_json_when_wants_json(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = CartItem::factory()->forProduct($product, 2)->forUser($user)->create([
            'session_id' => session()->getId(),
        ]);

        $response = $this->patchJson(route('cart.update', $item), ['quantity' => 3]);

        $response->assertOk();
        $response->assertJson(['result' => true, 'message' => 'Корзина обновлена.']);
    }

    public function test_remove_returns_json_when_wants_json(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = CartItem::factory()->forProduct($product, 1)->forUser($user)->create([
            'session_id' => session()->getId(),
        ]);

        $response = $this->deleteJson(route('cart.remove', $item));

        $response->assertOk();
        $response->assertJson(['result' => true]);
    }

    public function test_clear_returns_json_when_wants_json(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('cart.clear'));

        $response->assertOk();
        $response->assertJson(['result' => true, 'cartCount' => 0]);
    }

    public function test_update_returns_403_for_other_users_cart_item(): void
    {
        $user = \App\Models\User::factory()->create();
        $otherUser = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = CartItem::factory()->forProduct($product, 1)->forUser($otherUser)->create([
            'session_id' => 'other-session',
        ]);

        $response = $this->patch(route('cart.update', $item), ['quantity' => 2]);

        $response->assertStatus(403);
    }

    public function test_remove_returns_403_for_other_users_cart_item(): void
    {
        $user = \App\Models\User::factory()->create();
        $otherUser = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = CartItem::factory()->forProduct($product, 1)->forUser($otherUser)->create([
            'session_id' => 'other-session',
        ]);

        $response = $this->delete(route('cart.remove', $item));

        $response->assertStatus(403);
    }

    public function test_add_service_validates_quantity_max(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $service = Service::factory()->create();

        $response = $this->post(route('cart.add-service', $service), ['quantity' => 100]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_guest_can_update_and_remove_cart_item(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post(route('cart.add-product', $product), ['quantity' => 2]);

        $item = CartItem::where('product_id', $product->id)->whereNull('user_id')->first();
        $this->assertNotNull($item);

        $response = $this->patch(route('cart.update', $item), ['quantity' => 5]);
        $response->assertRedirect(route('cart.index'));
        $this->assertSame(5, $item->fresh()->quantity);

        $response = $this->delete(route('cart.remove', $item));
        $response->assertRedirect(route('cart.index'));
        $this->assertNull(CartItem::find($item->id));
    }

    public function test_update_quantity_to_zero_removes_item(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = CartItem::factory()->forProduct($product, 2)->forUser($user)->create([
            'session_id' => session()->getId(),
        ]);

        $response = $this->patch(route('cart.update', $item), ['quantity' => 0]);

        $response->assertRedirect(route('cart.index'));
        $this->assertNull(CartItem::find($item->id));
    }
}
