<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private const STATUSES = ['new', 'paid', 'processing', 'shipped', 'completed', 'completed', 'completed'];

    public function run(): void
    {
        $users = User::where('is_admin', false)->limit(25)->get();
        $products = Product::where('in_stock', true)->get();
        $services = Service::all();

        if ($products->isEmpty() && $services->isEmpty()) {
            return;
        }

        $ordersToCreate = random_int(45, 75);
        $created = 0;

        for ($i = 0; $i < $ordersToCreate * 2 && $created < $ordersToCreate; $i++) {
            $user = $users->random();
            $items = $this->makeItems($products, $services);
            if ($items === []) {
                continue;
            }
            $total = array_sum(array_column($items, 'subtotal'));
            $deliveryCost = $total >= 3000 ? 0 : 300;
            $total += $deliveryCost;

            $daysAgo = random_int(0, 60);
            $orderDate = Carbon::now()->subDays($daysAgo)->subHours(random_int(0, 23))->subMinutes(random_int(0, 59));

            $order = Order::create([
                'order_number' => $this->uniqueOrderNumber(),
                'user_id' => $user->id,
                'status' => (string) fake()->randomElement(self::STATUSES),
                'total' => $total,
                'contact_info' => ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? '+79001234567'],
                'delivery_info' => ['address' => 'г. Ульяновск, ул. Примерная, д. ' . random_int(1, 50), 'delivery_cost' => $deliveryCost],
                'payment_info' => ['method' => fake()->randomElement(['cash', 'card'])],
                'comment' => fake()->optional(0.3)->sentence(6),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
            $created++;
        }
    }

    /** @return list<array{product_id: null, service_id: int, quantity: int, price: float, subtotal: float}>|list<array{product_id: int, service_id: null, quantity: int, price: float, subtotal: float}> */
    private function makeItems($products, $services): array
    {
        $items = [];
        $count = random_int(1, 5);
        $haveProducts = $products->isNotEmpty();
        $haveServices = $services->isNotEmpty();

        for ($i = 0; $i < $count; $i++) {
            if ($haveProducts && (!$haveServices || random_int(0, 1) === 0)) {
                $product = $products->random();
                $qty = random_int(1, 2);
                $price = (float) $product->price;
                $items[] = ['product_id' => $product->id, 'service_id' => null, 'quantity' => $qty, 'price' => $price, 'subtotal' => $price * $qty];
            } elseif ($haveServices) {
                $service = $services->random();
                $qty = 1;
                $price = (float) ($service->price ?? 0);
                if ($price <= 0) {
                    $price = 500.0;
                }
                $items[] = ['product_id' => null, 'service_id' => $service->id, 'quantity' => $qty, 'price' => $price, 'subtotal' => $price * $qty];
            }
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
