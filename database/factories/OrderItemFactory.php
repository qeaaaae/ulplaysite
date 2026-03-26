<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'service_id' => null,
            'quantity' => fake()->numberBetween(1, 5),
            'price' => $product->price,
        ];
    }

    public function forProduct(Product $product, int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'service_id' => null,
            'quantity' => $quantity,
            'price' => $product->price,
        ]);
    }

    public function forService(Service $service, int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => null,
            'service_id' => $service->id,
            'quantity' => $quantity,
            'price' => 1000,
        ]);
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }
}
