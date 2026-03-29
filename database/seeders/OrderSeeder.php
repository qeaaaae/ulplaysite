<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private const STATUSES = ['new', 'paid', 'processing', 'shipped', 'completed', 'completed', 'completed'];

    public function run(): void
    {
        $users = User::where('is_admin', false)->limit(25)->get();
        $products = Product::where('in_stock', true)->get();

        if ($products->isEmpty()) {
            return;
        }

        $ordersToCreate = random_int(45, 75);
        $created = 0;

        for ($i = 0; $i < $ordersToCreate * 2 && $created < $ordersToCreate; $i++) {
            $user = $users->random();
            $items = $this->makeItems($products);
            if ($items === []) {
                continue;
            }
            $subtotal = array_sum(array_column($items, 'subtotal'));
            $deliveryType = fake()->randomElement(['delivery', 'pickup']);
            $deliveryCost = $deliveryType === 'pickup' ? 0 : ($subtotal >= 3000 ? 0 : 300);
            $total = $subtotal + $deliveryCost;

            $deliveryInfo = [
                'type' => $deliveryType,
                'delivery_cost' => $deliveryCost,
            ];
            if ($deliveryType === 'delivery') {
                $deliveryInfo['address'] = 'г. Ульяновск, ул. Примерная, д. ' . random_int(1, 50);
            }

            $order = Order::create([
                'order_number' => $this->uniqueOrderNumber(),
                'user_id' => $user->id,
                'status' => (string) fake()->randomElement(self::STATUSES),
                'total' => $total,
                'contact_info' => ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? '+79001234567'],
                'delivery_info' => $deliveryInfo,
                'payment_info' => ['method' => fake()->randomElement(['cash', 'card'])],
                'comment' => fake()->optional(0.3)->sentence(6),
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'service_id' => null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
            $created++;
        }
    }

    /** @return list<array{product_id: int, quantity: int, price: float, subtotal: float}> */
    private function makeItems($products): array
    {
        $items = [];
        $count = random_int(1, 5);

        for ($i = 0; $i < $count; $i++) {
            $product = $products->random();
            $qty = random_int(1, 2);
            $price = (float) $product->price;
            $items[] = ['product_id' => $product->id, 'quantity' => $qty, 'price' => $price, 'subtotal' => $price * $qty];
        }

        return $items;
    }

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'UL-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
