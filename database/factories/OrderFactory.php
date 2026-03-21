<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'UL-' . str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'status' => 'new',
            'total' => fake()->randomFloat(2, 500, 10000),
            'contact_info' => [
                'name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
            ],
            'delivery_info' => ['address' => fake()->address(), 'delivery_cost' => 300],
            'payment_info' => ['method' => fake()->randomElement(['cash', 'card'])],
            'comment' => null,
        ];
    }
}
