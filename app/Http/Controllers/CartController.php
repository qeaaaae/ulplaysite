<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart
    ) {}

    public function index(): View
    {
        $items = $this->cart->getItems();
        $total = $this->cart->total();

        return view('cart.index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function addProduct(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        if (! $product->in_stock) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар недоступен для заказа',
                    'errors' => ['quantity' => ['Товар временно отсутствует в наличии.']],
                ], 422);
            }

            return redirect()->back()->withErrors(['quantity' => 'Товар временно отсутствует в наличии.']);
        }

        $stock = max(0, (int) $product->stock);
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:' . $stock],
        ], ['quantity.max' => 'Недостаточно товара на складе (в наличии: ' . $stock . ').']);
        $this->cart->addProduct($product, (int) ($request->quantity ?? 1));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cartCount' => $this->cart->count(),
                'message' => 'Товар добавлен в корзину',
            ]);
        }

        return redirect()->back()->with('message', 'Товар добавлен в корзину');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->authorizeCartItem($cartItem);
        $max = max(0, (int) $cartItem->product->stock);
        $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:' . $max]]);
        $this->cart->updateQuantity($cartItem, (int) $request->quantity);

        if ($request->wantsJson()) {
            $items = $this->cart->getItems();
            $total = $this->cart->total();

            return response()->json([
                'result' => true,
                'message' => 'Корзина обновлена.',
                'cartCount' => $this->cart->count(),
                'html' => view('cart._cart-content', [
                    'items' => $items,
                    'total' => $total,
                ])->render(),
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function remove(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->authorizeCartItem($cartItem);
        $this->cart->remove($cartItem);

        if ($request->wantsJson()) {
            $items = $this->cart->getItems();
            $total = $this->cart->total();

            return response()->json([
                'result' => true,
                'message' => 'Товар удален из корзины.',
                'cartCount' => $this->cart->count(),
                'html' => view('cart._cart-content', [
                    'items' => $items,
                    'total' => $total,
                ])->render(),
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cart->clear();

        if ($request->wantsJson()) {
            $items = $this->cart->getItems();
            $total = $this->cart->total();

            return response()->json([
                'result' => true,
                'message' => 'Корзина очищена',
                'cartCount' => $this->cart->count(),
                'html' => view('cart._cart-content', [
                    'items' => $items,
                    'total' => $total,
                ])->render(),
            ]);
        }

        return redirect()->route('cart.index')->with('message', 'Корзина очищена');
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        if (Auth::check()) {
            $userId = Auth::id();
            if ($userId === null || (int) $cartItem->user_id !== (int) $userId) {
                abort(403, 'Этот товар не в вашей корзине.');
            }
        } else {
            $sessionId = $this->cart->getSessionId();
            if ($cartItem->user_id !== null || $cartItem->session_id !== $sessionId) {
                abort(403, 'Этот товар не в вашей корзине.');
            }
        }
    }
}
