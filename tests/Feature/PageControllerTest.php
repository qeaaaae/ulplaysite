<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PageControllerTest extends TestCase
{
    public function test_about_returns_200(): void
    {
        $response = $this->get(route('about'));

        $response->assertStatus(200);
    }

    public function test_delivery_returns_200(): void
    {
        $response = $this->get(route('delivery'));

        $response->assertStatus(200);
    }

    public function test_contacts_returns_200(): void
    {
        $response = $this->get(route('contacts'));

        $response->assertStatus(200);
    }
}
