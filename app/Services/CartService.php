<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getSessionId(): string
    {
        return session()->getId();
    }

    public function getItems(): \Illuminate\Database\Eloquent\Collection
    {
        $query = CartItem::query()
            ->with(['product.images', 'service.images'])
            ->where(function ($q) {
                if (Auth::check()) {
                    $q->where('user_id', Auth::id());
                } else {
                    $q->where('session_id', $this->getSessionId());
                }
            });

        return $query->get()->filter(fn (CartItem $item) => $item->product || $item->service);
    }

    public function count(): int
    {
        $query = CartItem::query()
            ->where(function ($q) {
                if (Auth::check()) {
                    $q->where('user_id', Auth::id());
                } else {
                    $q->where('session_id', $this->getSessionId());
                }
            });

        return (int) $query->sum('quantity');
    }

    public function addProduct(Product $product, int $quantity = 1): CartItem
    {
        $maxQty = max(0, (int) $product->stock);
        $quantity = min($quantity, $maxQty);
        $item = $this->findProduct($product->id);
        if ($item) {
            $item->quantity = min($item->quantity + $quantity, $maxQty);
            $item->save();
            return $item;
        }
        return CartItem::create([
            'session_id' => $this->getSessionId(),
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'service_id' => null,
            'quantity' => $quantity,
        ]);
    }

    public function addService(Service $service, int $quantity = 1): CartItem
    {
        $item = $this->findService($service->id);
        if ($item) {
            $item->quantity += $quantity;
            $item->save();
            return $item;
        }
        return CartItem::create([
            'session_id' => $this->getSessionId(),
            'user_id' => Auth::id(),
            'product_id' => null,
            'service_id' => $service->id,
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): bool
    {
        if ($quantity < 1) {
            return $item->delete();
        }
        if ($item->product_id && $item->product) {
            $quantity = min($quantity, max(0, (int) $item->product->stock));
        } else {
            $quantity = min($quantity, 99);
        }
        $item->quantity = $quantity;
        return $item->save();
    }

    public function remove(CartItem $item): bool
    {
        return $item->delete();
    }

    public function clear(): int
    {
        $query = CartItem::query()->where(function ($q) {
            if (Auth::check()) {
                $q->where('user_id', Auth::id());
            } else {
                $q->where('session_id', $this->getSessionId());
            }
        });
        $count = $query->count();
        $query->delete();
        return $count;
    }

    public function total(): float
    {
        return $this->getItems()->sum(fn (CartItem $item) => $item->subtotal);
    }

    private function findProduct(int $productId): ?CartItem
    {
        $query = CartItem::where('product_id', $productId)
            ->whereNull('service_id');

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', $this->getSessionId());
        }
        return $query->first();
    }

    private function findService(int $serviceId): ?CartItem
    {
        $query = CartItem::where('service_id', $serviceId)
            ->whereNull('product_id');

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', $this->getSessionId());
        }
        return $query->first();
    }

    public function mergeSessionToUser(int $userId, ?string $sessionId = null): int
    {
        $sessionId = $sessionId ?? $this->getSessionId();
        $items = CartItem::where('session_id', $sessionId)->whereNull('user_id')->get();
        $merged = 0;
        foreach ($items as $item) {
            $existing = CartItem::where('user_id', $userId)
                ->where(function ($q) use ($item) {
                    if ($item->product_id) {
                        $q->where('product_id', $item->product_id)->whereNull('service_id');
                    } else {
                        $q->where('service_id', $item->service_id)->whereNull('product_id');
                    }
                })
                ->first();
            if ($existing) {
                $newQty = $existing->quantity + $item->quantity;
                if ($existing->product_id && $existing->product) {
                    $newQty = min($newQty, max(0, (int) $existing->product->stock));
                } else {
                    $newQty = min($newQty, 99);
                }
                $existing->quantity = $newQty;
                $existing->save();
                $item->delete();
            } else {
                $item->update(['user_id' => $userId]);
            }
            $merged++;
        }
        return $merged;
    }

    public function restoreGuestCartToUser(int $userId, array $products, array $services): void
    {
        foreach ($products as $p) {
            $productId = (int) ($p['id'] ?? $p['product_id'] ?? 0);
            $qty = max(1, min(99, (int) ($p['quantity'] ?? 1)));
            if ($productId < 1) continue;
            $product = Product::find($productId);
            if (!$product || !$product->in_stock) continue;
            $qty = min($qty, max(0, (int) $product->stock));
            $existing = CartItem::where('user_id', $userId)
                ->where('product_id', $productId)
                ->whereNull('service_id')
                ->first();
            if ($existing) {
                $existing->quantity = min($existing->quantity + $qty, max(0, (int) $product->stock));
                $existing->save();
            } else {
                CartItem::create([
                    'session_id' => $this->getSessionId(),
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'service_id' => null,
                    'quantity' => $qty,
                ]);
            }
        }
        foreach ($services as $s) {
            $serviceId = (int) ($s['id'] ?? $s['service_id'] ?? 0);
            $qty = max(1, min(99, (int) ($s['quantity'] ?? 1)));
            if ($serviceId < 1) continue;
            $service = Service::find($serviceId);
            if (!$service) continue;
            $existing = CartItem::where('user_id', $userId)
                ->where('service_id', $serviceId)
                ->whereNull('product_id')
                ->first();
            if ($existing) {
                $existing->quantity = min($existing->quantity + $qty, 99);
                $existing->save();
            } else {
                CartItem::create([
                    'session_id' => $this->getSessionId(),
                    'user_id' => $userId,
                    'product_id' => null,
                    'service_id' => $serviceId,
                    'quantity' => $qty,
                ]);
            }
        }
    }
}
