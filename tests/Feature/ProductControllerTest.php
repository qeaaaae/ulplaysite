<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
    }

    public function test_index_filters_by_category(): void
    {
        $category = Category::factory()->child()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('products.index', ['category' => $category->slug]));

        $response->assertStatus(200);
    }

    public function test_show_returns_200(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertStatus(200);
    }

    public function test_index_returns_json_when_wants_json(): void
    {
        Product::factory()->count(2)->create();

        $response = $this->getJson(route('products.index'));

        $response->assertOk();
        $response->assertJson(['result' => true]);
        $response->assertJsonStructure(['html']);
    }

    public function test_index_supports_sort_parameter(): void
    {
        Product::factory()->count(2)->create();

        $response = $this->get(route('products.index', ['sort' => 'price_asc']));

        $response->assertStatus(200);
    }

    public function test_reviews_index_returns_json_when_wants_json(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson(route('reviews.index.product', $product));

        $response->assertOk();
        $response->assertJson(['result' => true]);
        $response->assertJsonStructure(['html']);
    }
}
