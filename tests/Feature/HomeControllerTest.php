<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    public function test_home_returns_200(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }
}
