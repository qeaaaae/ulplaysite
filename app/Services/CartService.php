<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
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
            ->with(['product.images'])
            ->where(function ($q) {
                if (Auth::check()) {
                    $q->where('user_id', Auth::id());
                } else {
                    $q->where('session_id', $this->getSessionId());
                }
            });

        return $query->get()->filter(fn (CartItem $item) => $item->product !== null);
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
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): bool
    {
        if ($quantity < 1) {
            return $item->delete();
        }
        $quantity = min($quantity, max(0, (int) $item->product->stock));
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
        $query = CartItem::where('product_id', $productId);

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
                ->where('product_id', $item->product_id)
                ->first();
            if ($existing) {
                $newQty = $existing->quantity + $item->quantity;
                $newQty = min($newQty, max(0, (int) $existing->product->stock));
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
            if ($productId < 1) {
                continue;
            }
            $product = Product::find($productId);
            if (! $product || ! $product->in_stock) {
                continue;
            }
            $qty = min($qty, max(0, (int) $product->stock));
            $existing = CartItem::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();
            if ($existing) {
                $existing->quantity = min($existing->quantity + $qty, max(0, (int) $product->stock));
                $existing->save();
            } else {
                CartItem::create([
                    'session_id' => $this->getSessionId(),
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $qty,
                ]);
            }
        }
    }
}
