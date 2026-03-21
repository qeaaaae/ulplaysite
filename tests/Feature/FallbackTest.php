<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class FallbackTest extends TestCase
{
    public function test_unknown_route_returns_404(): void
    {
        $response = $this->get('/non-existent-page-xyz-123');

        $response->assertStatus(404);
    }
}
