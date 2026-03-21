<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use Tests\TestCase;

class NewsControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        News::factory()->count(2)->create();

        $response = $this->get(route('news.index'));

        $response->assertStatus(200);
    }

    public function test_show_returns_200(): void
    {
        $news = News::factory()->create();

        $response = $this->get(route('news.show', $news));

        $response->assertStatus(200);
    }

    public function test_index_returns_json_when_wants_json(): void
    {
        News::factory()->count(2)->create();

        $response = $this->getJson(route('news.index'));

        $response->assertOk();
        $response->assertJson(['result' => true]);
        $response->assertJsonStructure(['html']);
    }
}
