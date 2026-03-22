<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Banner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();
        Banner::factory()->count(2)->create();

        $response = $this->get(route('admin.banners.index'));

        $response->assertStatus(200);
    }

    public function test_create_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.banners.create'));

        $response->assertStatus(200);
    }

    public function test_edit_returns_200(): void
    {
        $this->actingAsAdmin();
        $banner = Banner::factory()->create();

        $response = $this->get(route('admin.banners.edit', $banner));

        $response->assertStatus(200);
    }

    public function test_store_creates_banner(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.banners.store'), [
            'title' => 'New Banner',
            'description' => 'Desc',
            'link' => 'https://example.com',
            'active' => true,
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['title' => 'New Banner']);
    }

    public function test_store_creates_banner_with_image(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $image = UploadedFile::fake()->image('banner.jpg', 200, 200);

        $response = $this->post(route('admin.banners.store'), [
            'title' => 'Banner with image',
            'description' => 'Desc',
            'link' => 'https://example.com',
            'active' => true,
            'image' => $image,
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $banner = Banner::where('title', 'Banner with image')->first();
        $this->assertSame(1, $banner->images()->count());
    }

    public function test_update_modifies_banner(): void
    {
        $this->actingAsAdmin();
        $banner = Banner::factory()->create();

        $response = $this->patch(route('admin.banners.update', $banner), [
            'title' => 'Updated Banner',
            'description' => $banner->description,
            'link' => $banner->link,
            'active' => $banner->active,
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertSame('Updated Banner', $banner->fresh()->title);
    }

    public function test_destroy_deletes_banner(): void
    {
        $this->actingAsAdmin();
        $banner = Banner::factory()->create();

        $response = $this->delete(route('admin.banners.destroy', $banner));

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertNull(Banner::find($banner->id));
    }
}
