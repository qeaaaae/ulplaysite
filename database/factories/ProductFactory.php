<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->optional()->paragraph(),
            'price' => fake()->randomFloat(2, 100, 5000),
            'category_id' => Category::factory(),
            'in_stock' => true,
            'stock' => fake()->numberBetween(1, 99),
            'discount_percent' => fake()->optional(0.3)->numberBetween(5, 50),
            'is_new' => false,
            'is_recommended' => false,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'in_stock' => false,
            'stock' => 0,
        ]);
    }
}
