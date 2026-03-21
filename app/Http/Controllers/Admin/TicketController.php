<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::with(['user', 'images'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $query = (string) $request->q;
                $q->where(function ($q2) use ($query): void {
                    $q2->where('title', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%')
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $query . '%')->orWhere('email', 'like', '%' . $query . '%'));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.tickets.index', ['tickets' => $tickets]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'images', 'messages.senderUser']);

        return view('admin.tickets.show', ['ticket' => $ticket]);
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:new,in_progress,resolved,closed'],
        ]);

        $ticket->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('message', 'Статус тикета обновлён');
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $ticket->messages()->create([
            'sender_role' => 'admin',
            'sender_user_id' => $request->user()?->id,
            'content' => $validated['message'],
        ]);

        if ($ticket->status === 'new') {
            $ticket->update(['status' => 'in_progress']);
        }

        if ($ticket->user_id !== null) {
            UserNotification::query()->create([
                'user_id' => $ticket->user_id,
                'type' => 'ticket_reply',
                'title' => 'Ответ по вашему обращению',
                'body' => $validated['message'],
                'support_ticket_id' => $ticket->id,
                'url' => route('tickets.my.show', $ticket),
                'read_at' => null,
            ]);
        }

        return redirect()->back()->with('message', 'Ответ отправлен');
    }
}

