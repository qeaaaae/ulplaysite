<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\SupportTicket;
use App\Models\User;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    public function test_index_requires_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('admin.tickets.index'));

        $response->assertStatus(403);
    }

    public function test_index_shows_tickets_for_admin(): void
    {
        $this->actingAsAdmin();
        SupportTicket::factory()->count(2)->create();

        $response = $this->get(route('admin.tickets.index'));

        $response->assertStatus(200);
    }

    public function test_show_displays_ticket_for_admin(): void
    {
        $this->actingAsAdmin();
        $ticket = SupportTicket::factory()->create();

        $response = $this->get(route('admin.tickets.show', $ticket));

        $response->assertStatus(200);
    }

    public function test_update_status_changes_ticket_status(): void
    {
        $this->actingAsAdmin();
        $ticket = SupportTicket::factory()->create(['status' => 'new']);

        $response = $this->patch(route('admin.tickets.update-status', $ticket), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');
        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    public function test_reply_adds_message_to_ticket(): void
    {
        $this->actingAsAdmin();
        $ticket = SupportTicket::factory()->create();

        $response = $this->post(route('admin.tickets.reply', $ticket), [
            'message' => 'Admin reply text',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message');
        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'content' => 'Admin reply text',
        ]);
    }

    public function test_reply_validates_message_required(): void
    {
        $this->actingAsAdmin();
        $ticket = SupportTicket::factory()->create();

        $response = $this->post(route('admin.tickets.reply', $ticket), []);

        $response->assertSessionHasErrors('message');
    }

    public function test_update_status_validates_status(): void
    {
        $this->actingAsAdmin();
        $ticket = SupportTicket::factory()->create();

        $response = $this->patch(route('admin.tickets.update-status', $ticket), [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingAsAdmin();
        SupportTicket::factory()->create(['status' => 'new']);
        SupportTicket::factory()->create(['status' => 'resolved']);

        $response = $this->get(route('admin.tickets.index', ['status' => 'new']));

        $response->assertStatus(200);
    }
}
