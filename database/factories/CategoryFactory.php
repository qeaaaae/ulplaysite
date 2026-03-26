<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'parent_id' => null,
            'description' => fake()->optional()->sentence(),
            'is_featured' => false,
        ];
    }

    /** Дочерняя категория (для товаров). */
    public function child(?Category $parent = null): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            $p = $parent ?? Category::factory()->create();

            return [
                'parent_id' => $p->id,
            ];
        });
    }
}
