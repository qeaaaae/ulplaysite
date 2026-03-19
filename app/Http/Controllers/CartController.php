<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function addService(Request $request, Service $service): RedirectResponse
    {
        $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:99']]);
        $this->cart->addService($service, (int) ($request->quantity ?? 1));
        return redirect()->back()->with('message', 'Услуга добавлена в корзину');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->authorizeCartItem($cartItem);
        $max = $cartItem->product_id && $cartItem->product
            ? max(0, (int) $cartItem->product->stock)
            : 99;
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
        $cart = $this->cart->getItems();
        if (!$cart->contains('id', $cartItem->id)) {
            abort(403, 'Этот товар не в вашей корзине.');
        }
    }
}
