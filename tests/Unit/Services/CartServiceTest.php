<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = app(CartService::class);
    }

    public function test_add_product_creates_new_cart_item_for_guest(): void
    {
        Session::start();
        Auth::logout();

        $product = Product::factory()->create(['stock' => 10]);

        $item = $this->cart->addProduct($product, 2);

        $this->assertInstanceOf(CartItem::class, $item);
        $this->assertSame($product->id, $item->product_id);
        $this->assertNull($item->user_id);
        $this->assertSame(2, $item->quantity);
        $this->assertSame($this->cart->getSessionId(), $item->session_id);
    }

    public function test_add_product_increments_quantity_when_product_exists(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $product = Product::factory()->create(['stock' => 10]);

        $this->cart->addProduct($product, 2);
        $item = $this->cart->addProduct($product, 3);

        $this->assertSame(5, $item->quantity);
    }

    public function test_add_product_respects_stock_limit(): void
    {
        Auth::login(User::factory()->create());

        $product = Product::factory()->create(['stock' => 3]);

        $this->cart->addProduct($product, 2);
        $item = $this->cart->addProduct($product, 5);

        $this->assertSame(3, $item->quantity);
    }

    public function test_add_service_creates_new_cart_item(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $service = Service::factory()->create();

        $item = $this->cart->addService($service, 2);

        $this->assertInstanceOf(CartItem::class, $item);
        $this->assertSame($service->id, $item->service_id);
        $this->assertNull($item->product_id);
        $this->assertSame(2, $item->quantity);
    }

    public function test_add_service_increments_quantity_when_service_exists(): void
    {
        Auth::login(User::factory()->create());
        $service = Service::factory()->create();

        $this->cart->addService($service, 1);
        $item = $this->cart->addService($service, 2);

        $this->assertSame(3, $item->quantity);
    }

    public function test_update_quantity_updates_item(): void
    {
        Auth::login(User::factory()->create());
        $product = Product::factory()->create(['stock' => 10]);
        $item = $this->cart->addProduct($product, 5);

        $result = $this->cart->updateQuantity($item, 2);

        $this->assertTrue($result);
        $this->assertSame(2, $item->fresh()->quantity);
    }

    public function test_update_quantity_to_zero_deletes_item(): void
    {
        Auth::login(User::factory()->create());
        $product = Product::factory()->create(['stock' => 10]);
        $item = $this->cart->addProduct($product, 5);

        $result = $this->cart->updateQuantity($item, 0);

        $this->assertTrue($result);
        $this->assertNull(CartItem::find($item->id));
    }

    public function test_update_quantity_respects_product_stock(): void
    {
        Auth::login(User::factory()->create());
        $product = Product::factory()->create(['stock' => 5]);
        $item = $this->cart->addProduct($product, 3);

        $this->cart->updateQuantity($item, 10);

        $this->assertSame(5, $item->fresh()->quantity);
    }

    public function test_remove_deletes_item(): void
    {
        Auth::login(User::factory()->create());
        $item = $this->cart->addProduct(Product::factory()->create(), 1);

        $result = $this->cart->remove($item);

        $this->assertTrue($result);
        $this->assertNull(CartItem::find($item->id));
    }

    public function test_clear_removes_all_items(): void
    {
        Auth::login(User::factory()->create());
        $this->cart->addProduct(Product::factory()->create(), 1);
        $this->cart->addService(Service::factory()->create(), 1);

        $count = $this->cart->clear();

        $this->assertSame(2, $count);
        $this->assertSame(0, $this->cart->count());
    }

    public function test_count_returns_total_quantity(): void
    {
        Auth::login(User::factory()->create());
        $this->cart->addProduct(Product::factory()->create(), 2);
        $this->cart->addProduct(Product::factory()->create(), 3);

        $this->assertSame(5, $this->cart->count());
    }

    public function test_total_sums_subtotals(): void
    {
        Auth::login(User::factory()->create());
        $product = Product::factory()->create(['price' => 100, 'stock' => 10, 'discount_percent' => null]);
        $this->cart->addProduct($product, 2);

        $this->assertSame(200.0, $this->cart->total());
    }

    public function test_merge_session_to_user_moves_guest_items_to_user(): void
    {
        Session::start();
        Auth::logout();

        $product = Product::factory()->create();
        $guestItem = $this->cart->addProduct($product, 2);
        $sessionId = $guestItem->session_id;

        $user = User::factory()->create();
        $this->cart->mergeSessionToUser($user->id, $sessionId);

        $guestItem->refresh();
        $this->assertSame($user->id, $guestItem->user_id);
        $this->assertSame(2, $guestItem->quantity);
    }

    public function test_merge_session_to_user_merges_duplicate_products(): void
    {
        Session::start();
        Auth::logout();

        $product = Product::factory()->create();
        CartItem::factory()->forProduct($product, 2)->forSession('guest-session')->create(['user_id' => null]);

        $user = User::factory()->create();
        CartItem::factory()->forProduct($product, 1)->forUser($user)->create(['session_id' => 'any']);

        $this->cart->mergeSessionToUser($user->id, 'guest-session');

        $userItems = CartItem::where('user_id', $user->id)->get();
        $this->assertSame(1, $userItems->count());
        $this->assertSame(3, $userItems->first()->quantity);
    }
}
