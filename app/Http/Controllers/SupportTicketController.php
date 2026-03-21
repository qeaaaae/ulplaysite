<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SupportTicketTypeEnum;
use App\Models\SupportTicket;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function myIndex(Request $request)
    {
        $user = $request->user();

        $tickets = SupportTicket::query()
            ->with(['images'])
            ->withCount('messages')
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('tickets.index', [
            'tickets' => $tickets,
        ]);
    }

    public function myShow(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        abort_unless($ticket->user_id === $user->id, 403);

        $ticket->load(['images', 'messages.senderUser']);

        UserNotification::query()
            ->where('user_id', $user->id)
            ->where('support_ticket_id', $ticket->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', array_column(SupportTicketTypeEnum::cases(), 'value'))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()?->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        // Initial message in the dialog (from the ticket author).
        $ticket->messages()->create([
            'sender_role' => 'user',
            'sender_user_id' => $request->user()?->id,
            'content' => $validated['description'],
        ]);

        foreach (array_values($request->file('images', [])) as $index => $file) {
            $ticket->images()->create([
                'path' => $file->store('support-tickets', 'public'),
                'is_cover' => $index === 0,
                'position' => $index,
            ]);
        }

        return redirect()->back()->with('message', 'Заявка в техподдержку отправлена');
    }
}

