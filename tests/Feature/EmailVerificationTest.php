<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    public function test_verification_notice_returns_200_for_unverified(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    public function test_verification_verify_succeeds(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url);

        $response->assertRedirect(route('home'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verification_send_returns_back_with_message(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $response = $this->post(route('verification.send'));

        $response->assertRedirect();
        $response->assertSessionHas('message');
    }

    public function test_verification_send_returns_back_when_already_verified(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('verification.send'));

        $response->assertRedirect();
    }
}
