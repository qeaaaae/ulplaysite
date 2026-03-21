<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();
        News::factory()->count(2)->create();

        $response = $this->get(route('admin.news.index'));

        $response->assertStatus(200);
    }

    public function test_create_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.news.create'));

        $response->assertStatus(200);
    }

    public function test_edit_returns_200(): void
    {
        $this->actingAsAdmin();
        $news = News::factory()->create();

        $response = $this->get(route('admin.news.edit', $news));

        $response->assertStatus(200);
    }

    public function test_store_creates_news(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.news.store'), [
            'title' => 'New News',
            'description' => 'Desc',
            'content' => 'Content',
            'published_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertDatabaseHas('news', ['title' => 'New News']);
    }

    public function test_update_modifies_news(): void
    {
        $this->actingAsAdmin();
        $news = News::factory()->create();

        $response = $this->patch(route('admin.news.update', $news), [
            'title' => 'Updated News',
            'description' => $news->description,
            'content' => $news->content,
            'published_at' => $news->published_at?->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $this->assertSame('Updated News', $news->fresh()->title);
    }

    public function test_destroy_deletes_news(): void
    {
        $this->actingAsAdmin();
        $news = News::factory()->create();

        $response = $this->delete(route('admin.news.destroy', $news));

        $response->assertRedirect(route('admin.news.index'));
        $this->assertNull(News::find($news->id));
    }

    public function test_update_rejects_new_images_when_news_has_5_images(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $news = News::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $news->images()->create([
                'path' => "news/img{$i}.jpg",
                'is_cover' => $i === 0,
                'position' => $i,
            ]);
        }

        $newImage = UploadedFile::fake()->image('extra.jpg', 200, 200);

        $response = $this->patch(route('admin.news.update', $news), [
            'title' => $news->title,
            'description' => $news->description,
            'content' => $news->content,
            'published_at' => $news->published_at?->format('Y-m-d H:i:s'),
            'images' => [$newImage],
        ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_update_allows_add_after_delete_images(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $news = News::factory()->create();
        $img1 = $news->images()->create(['path' => 'news/a.jpg', 'is_cover' => true, 'position' => 0]);

        $newImage = UploadedFile::fake()->image('new.jpg', 200, 200);

        $response = $this->patch(route('admin.news.update', $news), [
            'title' => $news->title,
            'description' => $news->description,
            'content' => $news->content,
            'published_at' => $news->published_at?->format('Y-m-d H:i:s'),
            'delete_images' => [$img1->id],
            'images' => [$newImage],
        ]);

        $response->assertRedirect(route('admin.news.index'));
        $news->refresh();
        $this->assertSame(1, $news->images()->count());
    }
}
