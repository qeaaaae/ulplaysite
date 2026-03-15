<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with('user')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('order_number', 'like', '%' . $request->q . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('email', 'like', '%' . $request->q . '%'));
            }))
            ->latest()
            ->paginate(15)
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

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('message', 'Статус заказа обновлён');
    }
}
