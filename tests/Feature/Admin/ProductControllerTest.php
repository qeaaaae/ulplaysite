<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    public function test_index_requires_admin(): void
    {
        $this->actingAs(\App\Models\User::factory()->create(['is_admin' => false]));

        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(403);
    }

    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();
        Product::factory()->count(2)->create();

        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(200);
    }

    public function test_index_filters_by_search(): void
    {
        $this->actingAsAdmin();
        Product::factory()->create(['title' => 'Unique Foo Product']);
        Product::factory()->create(['title' => 'Other Bar']);

        $response = $this->get(route('admin.products.index', ['q' => 'Foo']));

        $response->assertStatus(200);
        $response->assertSee('Unique Foo Product');
        $response->assertDontSee('Other Bar');
    }

    public function test_create_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.products.create'));

        $response->assertStatus(200);
    }

    public function test_store_creates_product(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $response = $this->post(route('admin.products.store'), [
            'title' => 'New Product',
            'description' => 'Desc',
            'price' => 1000,
            'category_id' => $category->id,
            'stock' => 10,
            'in_stock' => true,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['title' => 'New Product']);
    }

    public function test_store_creates_product_with_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->actingAsAdmin();
        $category = Category::factory()->create();
        $image = \Illuminate\Http\UploadedFile::fake()->image('product.jpg', 200, 200);

        $response = $this->post(route('admin.products.store'), [
            'title' => 'Product with image',
            'description' => 'Desc',
            'price' => 500,
            'category_id' => $category->id,
            'stock' => 5,
            'in_stock' => true,
            'images' => [$image],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::where('title', 'Product with image')->first();
        $this->assertSame(1, $product->images()->count());
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.products.store'), []);

        $response->assertSessionHasErrors(['title', 'price', 'category_id', 'stock']);
    }

    public function test_edit_returns_200(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();

        $response = $this->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
    }

    public function test_update_modifies_product(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();

        $response = $this->patch(route('admin.products.update', $product), [
            'title' => 'Updated Title',
            'description' => $product->description,
            'price' => $product->price,
            'category_id' => $product->category_id,
            'stock' => $product->stock,
            'in_stock' => true,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame('Updated Title', $product->fresh()->title);
    }

    public function test_destroy_deletes_product(): void
    {
        $this->actingAsAdmin();
        $product = Product::factory()->create();

        $response = $this->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertNull(Product::find($product->id));
    }

    public function test_update_rejects_new_images_when_product_has_5_images(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $product->images()->create([
                'path' => "products/img{$i}.jpg",
                'is_cover' => $i === 0,
                'position' => $i,
            ]);
        }

        $newImage = UploadedFile::fake()->image('extra.jpg', 200, 200);

        $response = $this->patch(route('admin.products.update', $product), [
            'title' => $product->title,
            'description' => $product->description,
            'price' => $product->price,
            'category_id' => $product->category_id,
            'stock' => $product->stock,
            'in_stock' => true,
            'images' => [$newImage],
        ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_update_allows_add_after_delete_images(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $product = Product::factory()->create();
        $img1 = $product->images()->create(['path' => 'products/a.jpg', 'is_cover' => true, 'position' => 0]);
        $img2 = $product->images()->create(['path' => 'products/b.jpg', 'is_cover' => false, 'position' => 1]);
        $img3 = $product->images()->create(['path' => 'products/c.jpg', 'is_cover' => false, 'position' => 2]);

        $newImage = UploadedFile::fake()->image('new.jpg', 200, 200);

        $response = $this->patch(route('admin.products.update', $product), [
            'title' => $product->title,
            'description' => $product->description,
            'price' => $product->price,
            'category_id' => $product->category_id,
            'stock' => $product->stock,
            'in_stock' => true,
            'delete_images' => [$img1->id, $img2->id],
            'images' => [$newImage],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $product->refresh();
        $this->assertSame(2, $product->images()->count());
    }
}
