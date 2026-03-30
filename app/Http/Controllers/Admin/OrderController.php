<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserNotification;
use App\Support\StrHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with('user')->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $like = '%' . StrHelper::escapeForLike((string) $request->q) . '%';
                $q->where(function ($q2) use ($like): void {
                    $q2->where('order_number', 'like', $like)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
                });
            })
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

        if ($order->user_id !== null) {
            Cache::forget("user.{$order->user_id}.purchased_no_review");
        }

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

            $body = "Заказ {$order->order_number}: {$statusLabel}.";
            $notificationUrl = route('orders.show', $order);

            if ($newStatus === 'completed') {
                $title = 'Заказ выполнен';
                $body = "Заказ {$order->order_number} выполнен. Можете оставить отзыв на купленные товары — раздел «Оставить отзыв» на странице «Мои заказы».";
                $notificationUrl = route('orders.index') . '#leave-review';
            }

            UserNotification::query()->create([
                'user_id' => $order->user_id,
                'type' => 'order_status_changed',
                'title' => $title,
                'body' => $body,
                'support_ticket_id' => null,
                'url' => $notificationUrl,
                'read_at' => null,
            ]);
        }

        return redirect()->back()->with('message', 'Статус заказа обновлён');
    }
}
