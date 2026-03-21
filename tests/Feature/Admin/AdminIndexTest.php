<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminIndexTest extends TestCase
{
    public function test_admin_redirects_to_products(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.index'));

        $response->assertRedirect(route('admin.products.index'));
    }
}
