<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SupportTicketTypeEnum;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    public function test_store_requires_auth(): void
    {
        $response = $this->post(route('support-tickets.store'), [
            'type' => SupportTicketTypeEnum::TECHNICAL_ISSUE->value,
            'title' => 'Test ticket',
            'description' => 'Test description',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_store_creates_ticket_with_auth(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('support-tickets.store'), [
            'type' => SupportTicketTypeEnum::TECHNICAL_ISSUE->value,
            'title' => 'Test ticket',
            'description' => 'Test description',
            'images' => [],
        ]);

        $response->assertRedirect(route('tickets.my.index'));
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('support_tickets', ['title' => 'Test ticket', 'user_id' => $user->id]);
    }

    public function test_store_validates_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('support-tickets.store'), [
            'type' => 'invalid_type',
            'title' => 'Test',
            'description' => 'Test desc',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_store_validates_title_and_description(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('support-tickets.store'), [
            'type' => SupportTicketTypeEnum::TECHNICAL_ISSUE->value,
        ]);

        $response->assertSessionHasErrors(['title', 'description']);
    }

    public function test_store_validates_description_max_length(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('support-tickets.store'), [
            'type' => SupportTicketTypeEnum::TECHNICAL_ISSUE->value,
            'title' => 'Test',
            'description' => str_repeat('a', 3001),
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_support_create_requires_auth(): void
    {
        $response = $this->get(route('support.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_my_index_requires_auth(): void
    {
        $response = $this->get(route('tickets.my.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_my_index_shows_user_tickets(): void
    {
        $user = User::factory()->create();
        SupportTicket::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->get(route('tickets.my.index'));

        $response->assertStatus(200);
    }

    public function test_my_show_returns_403_for_other_users_ticket(): void
    {
        $ticket = SupportTicket::factory()->create(['user_id' => User::factory()->create()->id]);
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('tickets.my.show', $ticket));

        $response->assertStatus(403);
    }

    public function test_my_show_allows_owner_to_view(): void
    {
        $user = User::factory()->create();
        $ticket = SupportTicket::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->get(route('tickets.my.show', $ticket));

        $response->assertStatus(200);
    }

    public function test_store_creates_ticket_with_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $image = \Illuminate\Http\UploadedFile::fake()->image('screenshot.png', 100, 100);

        $response = $this->post(route('support-tickets.store'), [
            'type' => SupportTicketTypeEnum::TECHNICAL_ISSUE->value,
            'title' => 'Ticket with image',
            'description' => 'Description',
            'images' => [$image],
        ]);

        $response->assertRedirect(route('tickets.my.index'));
        $response->assertSessionHas('message');
        $ticket = \App\Models\SupportTicket::where('title', 'Ticket with image')->first();
        $this->assertNotNull($ticket);
        $this->assertSame(1, $ticket->images()->count());
    }
}
