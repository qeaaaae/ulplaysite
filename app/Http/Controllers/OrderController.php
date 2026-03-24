<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orderService
    ) {}

    public function checkout(): View|RedirectResponse
    {
        $items = $this->cart->getItems();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $subtotal = $items->sum(fn($i) => $i->subtotal);
        $user = Auth::user();
        $lastOrder = $user?->orders()->latest()->first();
        $deliveryCost = 300;
        if ($subtotal >= 3000) {
            $deliveryCost = 0;
        }
        $total = $subtotal + $deliveryCost;

        return view('orders.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryCost' => $deliveryCost,
            'total' => $total,
            'user' => $user,
            'lastOrder' => $lastOrder,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $order = $this->orderService->create(
                contactInfo: [
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                ],
                deliveryInfo: [
                    'type' => $validated['delivery_type'],
                    'address' => $validated['address'] ?? null,
                ],
                paymentInfo: ['method' => $validated['payment']],
                comment: $validated['comment'] ?? null
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        session()->put('order_view_' . $order->id, true);

        return redirect()->route('orders.show', $order)->with('message', 'Заказ оформлен');
    }

    public function show(Order $order): View|RedirectResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'items.service']);

        return view('orders.show', ['order' => $order]);
    }

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $orders = $user->orders()->with('items.product', 'items.service')->latest()->paginate(10);
        $purchasedWithoutReview = $user->getPurchasedWithoutReview();

        return view('orders.index', [
            'orders' => $orders,
            'purchasedWithoutReview' => $purchasedWithoutReview,
        ]);
    }
}
