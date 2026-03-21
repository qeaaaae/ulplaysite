<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use Tests\TestCase;

class ServiceControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        Service::factory()->count(2)->create();

        $response = $this->get(route('services.index'));

        $response->assertStatus(200);
    }

    public function test_show_returns_200(): void
    {
        $service = Service::factory()->create();

        $response = $this->get(route('services.show', $service));

        $response->assertStatus(200);
    }

    public function test_index_returns_json_when_wants_json(): void
    {
        Service::factory()->count(2)->create();

        $response = $this->getJson(route('services.index'));

        $response->assertOk();
        $response->assertJson(['result' => true]);
        $response->assertJsonStructure(['html']);
    }
}
