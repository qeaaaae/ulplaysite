<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'category_id' => fn () => Category::query()->whereNull('parent_id')->inRandomOrder()->value('id'),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->optional()->paragraph(),
            'content' => fake()->optional()->paragraphs(3, true),
        ];
    }
}
