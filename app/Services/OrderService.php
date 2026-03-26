<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\WebPushService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private CartService $cart
    ) {}

    public function create(array $contactInfo, array $deliveryInfo, array $paymentInfo, ?string $comment = null): Order
    {
        $items = $this->cart->getItems();
        if ($items->isEmpty()) {
            throw new \RuntimeException('Корзина пуста.');
        }

        return DB::transaction(function () use ($items, $contactInfo, $deliveryInfo, $paymentInfo, $comment) {
            $total = 0;
            $orderItems = [];

            foreach ($items as $cartItem) {
                $price = $cartItem->price;
                $subtotal = $price * $cartItem->quantity;
                $total += $subtotal;
                $orderItems[] = [
                    'product_id' => $cartItem->product_id,
                    'service_id' => null,
                    'quantity' => $cartItem->quantity,
                    'price' => $price,
                ];
            }

            $deliveryType = $deliveryInfo['type'] ?? 'delivery';
            $deliveryCost = $deliveryType === 'pickup' ? 0 : ($total >= 3000 ? 0 : 300);
            $total += $deliveryCost;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => Auth::id(),
                'status' => 'new',
                'total' => $total,
                'contact_info' => $contactInfo,
                'delivery_info' => array_merge($deliveryInfo, ['delivery_cost' => $deliveryCost, 'type' => $deliveryType]),
                'payment_info' => $paymentInfo,
                'comment' => $comment,
            ]);

            foreach ($orderItems as $data) {
                $order->items()->create($data);
            }

            foreach ($items as $cartItem) {
                $cartItem->delete();
            }

            try {
                app(WebPushService::class)->notifyNewOrder($order);
            } catch (\Throwable $e) {
                report($e);
            }

            if ($order->user_id !== null) {
                Cache::forget("user.{$order->user_id}.purchased_no_review");
            }

            return $order;
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'UL-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
