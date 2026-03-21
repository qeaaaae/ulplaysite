<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Call the given URI and return the Response. Preserves session cookie for subsequent requests.
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): TestResponse
    {
        $response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);

        $sessionCookie = config('session.cookie');
        $cookie = $response->getCookie($sessionCookie, false);
        if ($cookie !== null) {
            $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());
        }

        return $response;
    }

    protected function actingAsUser(?array $overrides = []): static
    {
        $user = User::factory()->create(array_merge(['is_admin' => false], $overrides));

        return $this->actingAs($user);
    }

    protected function actingAsAdmin(?array $overrides = []): static
    {
        $user = User::factory()->create(array_merge(['is_admin' => true], $overrides));

        return $this->actingAs($user);
    }
}
