<?php

declare(strict_types=1);

namespace Tests\Unit\Console;

use Tests\TestCase;

class GenerateVapidKeysCommandTest extends TestCase
{
    public function test_command_exists_and_is_invokable(): void
    {
        $this->artisan('webpush:vapid');

        $this->assertTrue(true);
    }
}
