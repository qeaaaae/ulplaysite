<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\ImageService;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function create(): View
    {
        return view('tickets.create', ['metaTitle' => 'Поддержка']);
    }

    public function myIndex(Request $request)
    {
        $user = $request->user();

        $tickets = SupportTicket::query()
            ->forUser($user->id)
            ->with(['images', 'service'])
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('tickets.index', [
            'metaTitle' => 'Мои обращения',
            'tickets' => $tickets,
        ]);
    }

    public function myShow(Request $request, SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['images', 'messages.senderUser', 'service']);

        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->where('support_ticket_id', $ticket->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('tickets.show', [
            'metaTitle' => "Обращение #{$ticket->id}",
            'ticket' => $ticket,
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'service_id' => $validated['service_id'] ?? null,
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
                'path' => $this->imageService->store($file, 'support-tickets'),
                'is_cover' => $index === 0,
                'position' => $index,
            ]);
        }

        app(WebPushService::class)->notifyNewTicket($ticket);

        // Дублируем в обычные уведомления для всех админов.
        $adminIds = User::query()
            ->where('is_admin', true)
            ->pluck('id');
        foreach ($adminIds as $adminId) {
            UserNotification::query()->create([
                'user_id' => $adminId,
                'type' => 'admin_new_ticket',
                'title' => 'Новый тикет',
                'body' => $ticket->title,
                'support_ticket_id' => $ticket->id,
                'url' => route('admin.tickets.show', $ticket),
                'read_at' => null,
            ]);
        }

        return redirect()->route('tickets.my.index')->with('message', 'Заявка в техподдержку отправлена');
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $ticket);

        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            if ($request->wantsJson()) {
                return response()->json(['result' => false, 'error' => 'Нельзя ответить в закрытом обращении'], 422);
            }

            return redirect()->back()->with('error', 'Нельзя ответить в закрытом обращении');
        }

        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $message = $ticket->messages()->create([
            'sender_role' => 'user',
            'sender_user_id' => $request->user()->id,
            'content' => strip_tags($validated['message']),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'result' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_role' => $message->sender_role,
                    'created_at' => $message->created_at->format(config('app.datetime_format')),
                ],
            ]);
        }

        return redirect()->back()->with('message', 'Сообщение отправлено');
    }
}

