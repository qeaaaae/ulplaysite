<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function create(): View
    {
        return view('tickets.create');
    }

    public function myIndex(Request $request)
    {
        $user = $request->user();

        $tickets = SupportTicket::query()
            ->forUser($user->id)
            ->with(['images'])
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('tickets.index', [
            'tickets' => $tickets,
        ]);
    }

    public function myShow(Request $request, SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['images', 'messages.senderUser']);

        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('support_ticket_id', $ticket->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
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
            'sender_user_id' => $request->user()->id,
            'content' => $validated['description'],
        ]);

        foreach (array_values($request->file('images', [])) as $index => $file) {
            $ticket->images()->create([
                'path' => $file->store('support-tickets', 'public'),
                'is_cover' => $index === 0,
                'position' => $index,
            ]);
        }

        return redirect()->route('tickets.my.index')->with('message', 'Заявка в техподдержку отправлена');
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            return redirect()->back()->with('error', 'Нельзя ответить в закрытом обращении');
        }

        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $ticket->messages()->create([
            'sender_role' => 'user',
            'sender_user_id' => $request->user()->id,
            'content' => strip_tags($validated['message']),
        ]);

        return redirect()->back()->with('message', 'Сообщение отправлено');
    }
}

