<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();
        Category::factory()->count(2)->create();

        $response = $this->get(route('admin.categories.index'));

        $response->assertStatus(200);
    }

    public function test_create_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.categories.create'));

        $response->assertStatus(200);
    }

    public function test_store_creates_category(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.categories.store'), [
            'name' => 'New Category',
            'description' => 'Desc',
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    public function test_store_creates_category_with_image(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $image = UploadedFile::fake()->image('cat.jpg', 200, 200);

        $response = $this->post(route('admin.categories.store'), [
            'name' => 'Category with image',
            'description' => 'Desc',
            'sort_order' => 1,
            'image' => $image,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $category = Category::where('name', 'Category with image')->first();
        $this->assertSame(1, $category->images()->count());
    }

    public function test_edit_returns_200(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $response = $this->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
    }

    public function test_update_modifies_category(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $response = $this->patch(route('admin.categories.update', $category), [
            'name' => 'Updated Category',
            'description' => $category->description,
            'sort_order' => max(1, $category->sort_order),
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertSame('Updated Category', $category->fresh()->name);
    }

    public function test_destroy_deletes_category(): void
    {
        $this->actingAsAdmin();
        $category = Category::factory()->create();

        $response = $this->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertNull(Category::find($category->id));
    }
}
