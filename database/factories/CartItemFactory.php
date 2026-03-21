<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'session_id' => fake()->uuid(),
            'user_id' => null,
            'product_id' => Product::factory(),
            'service_id' => null,
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }

    public function forProduct(Product $product, int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'service_id' => null,
            'quantity' => $quantity,
        ]);
    }

    public function forService(Service $service, int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => null,
            'service_id' => $service->id,
            'quantity' => $quantity,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forSession(string $sessionId): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => $sessionId,
            'user_id' => null,
        ]);
    }
}
