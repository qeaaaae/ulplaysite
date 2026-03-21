<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\TestCase;

class StatisticsControllerTest extends TestCase
{
    public function test_index_returns_200(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.statistics.index'));

        $response->assertStatus(200);
    }
}
