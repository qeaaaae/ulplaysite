<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $deliveryCost = $subtotal >= 3000 ? 0 : 300;
        $total = $subtotal + $deliveryCost;

        return view('orders.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryCost' => $deliveryCost,
            'total' => $total,
            'user' => Auth::user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            abort(403, 'Требуется авторизация.');
        }

        // Дублирующая защита: заказ создаём только для подтверждённого email.
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email'],
            'address' => ['required', 'string'],
            'payment' => ['required', 'in:cash,card'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $order = $this->orderService->create(
                contactInfo: [
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                ],
                deliveryInfo: ['address' => $validated['address']],
                paymentInfo: ['method' => $validated['payment']],
                comment: $validated['comment'] ?? null
            );
            if (! Auth::check()) {
                session()->put('order_view_' . $order->id, true);
            }
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('message', 'Заказ оформлен');
    }

    public function show(Order $order): View|RedirectResponse
    {
        $canView = (Auth::id() && $order->user_id === Auth::id())
            // Админ может просматривать любые заказы (например, по ссылке из push-уведомления)
            || (Auth::check() && Auth::user()?->is_admin)
            || session('order_view_' . $order->id);

        if (!$canView) {
            abort(403);
        }

        $order->load('items.product', 'items.service');

        return view('orders.show', ['order' => $order]);
    }

    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $orders = $user->orders()->with('items')->latest()->paginate(10);
        $purchasedWithoutReview = $user->getPurchasedWithoutReview();

        return view('orders.index', [
            'orders' => $orders,
            'purchasedWithoutReview' => $purchasedWithoutReview,
        ]);
    }
}
