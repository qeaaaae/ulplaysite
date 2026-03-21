<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();
        Service::factory()->count(2)->create();

        $response = $this->get(route('admin.services.index'));

        $response->assertStatus(200);
    }

    public function test_create_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.services.create'));

        $response->assertStatus(200);
    }

    public function test_edit_returns_200(): void
    {
        $this->actingAsAdmin();
        $service = Service::factory()->create();

        $response = $this->get(route('admin.services.edit', $service));

        $response->assertStatus(200);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.services.store'), []);

        $response->assertSessionHasErrors(['title', 'type']);
    }

    public function test_store_creates_service(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.services.store'), [
            'title' => 'New Service',
            'description' => 'Desc',
            'price' => 500,
            'type' => 'repair',
        ]);

        $response->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', ['title' => 'New Service']);
    }

    public function test_update_modifies_service(): void
    {
        $this->actingAsAdmin();
        $service = Service::factory()->create();

        $response = $this->patch(route('admin.services.update', $service), [
            'title' => 'Updated Service',
            'description' => $service->description,
            'price' => $service->price,
            'type' => $service->type,
        ]);

        $response->assertRedirect(route('admin.services.index'));
        $this->assertSame('Updated Service', $service->fresh()->title);
    }

    public function test_destroy_deletes_service(): void
    {
        $this->actingAsAdmin();
        $service = Service::factory()->create();

        $response = $this->delete(route('admin.services.destroy', $service));

        $response->assertRedirect(route('admin.services.index'));
        $this->assertNull(Service::find($service->id));
    }

    public function test_update_rejects_new_images_when_service_has_5_images(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $service = Service::factory()->create(['type' => 'repair']);
        for ($i = 0; $i < 5; $i++) {
            $service->images()->create([
                'path' => "services/img{$i}.jpg",
                'is_cover' => $i === 0,
                'position' => $i,
            ]);
        }

        $newImage = UploadedFile::fake()->image('extra.jpg', 200, 200);

        $response = $this->patch(route('admin.services.update', $service), [
            'title' => $service->title,
            'description' => $service->description,
            'price' => $service->price,
            'type' => $service->type,
            'images' => [$newImage],
        ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_update_allows_add_after_delete_images(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $service = Service::factory()->create(['type' => 'repair']);
        $img1 = $service->images()->create(['path' => 'services/a.jpg', 'is_cover' => true, 'position' => 0]);
        $img2 = $service->images()->create(['path' => 'services/b.jpg', 'is_cover' => false, 'position' => 1]);

        $newImage = UploadedFile::fake()->image('new.jpg', 200, 200);

        $response = $this->patch(route('admin.services.update', $service), [
            'title' => $service->title,
            'description' => $service->description,
            'price' => $service->price,
            'type' => $service->type,
            'delete_images' => [$img1->id],
            'images' => [$newImage],
        ]);

        $response->assertRedirect(route('admin.services.index'));
        $service->refresh();
        $this->assertSame(2, $service->images()->count());
    }
}
