<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with('user')->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('order_number', 'like', '%' . $request->q . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('email', 'like', '%' . $request->q . '%'));
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'items.service', 'user');

        return view('admin.orders.show', ['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate(['status' => ['required', 'string', 'in:new,paid,processing,shipped,completed,cancelled']]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);

        if ($order->user_id === null) {
            return redirect()->back()->with('message', 'Статус заказа обновлён');
        }

        $shouldNotify = ($oldStatus === 'new' && $newStatus !== 'new')
            || (in_array($oldStatus, ['paid', 'processing'], true) && $newStatus === 'shipped')
            || ($oldStatus === 'shipped' && $newStatus === 'completed')
            || ($newStatus === 'cancelled' && $oldStatus !== 'cancelled');

        if ($shouldNotify) {
            $statusLabel = match ($newStatus) {
                'paid' => 'Оплачен',
                'processing' => 'В обработке',
                'shipped' => 'Отправлен',
                'completed' => 'Выполнен',
                'cancelled' => 'Отменён',
                default => $newStatus,
            };

            $title = $newStatus === 'cancelled' ? 'Заказ отменён' : 'Статус заказа изменён';

            UserNotification::query()->create([
                'user_id' => $order->user_id,
                'type' => 'order_status_changed',
                'title' => $title,
                'body' => "Заказ {$order->order_number}: {$statusLabel}.",
                'support_ticket_id' => null,
                'url' => route('orders.show', $order),
                'read_at' => null,
            ]);
        }

        return redirect()->back()->with('message', 'Статус заказа обновлён');
    }
}
