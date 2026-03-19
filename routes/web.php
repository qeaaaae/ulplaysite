<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ServiceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news:slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/about', [\App\Http\Controllers\PageController::class, 'about'])->name('about');
Route::get('/delivery', [\App\Http\Controllers\PageController::class, 'delivery'])->name('delivery');
Route::get('/contacts', [\App\Http\Controllers\PageController::class, 'contacts'])->name('contacts');

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => redirect()->route('home'))->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::get('/register', fn () => redirect()->route('home'))->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:password');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update')->middleware('throttle:password');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index')->middleware('verified_if_auth');
Route::post('/cart/product/{product}', [\App\Http\Controllers\CartController::class, 'addProduct'])->name('cart.add-product')->middleware(['throttle:cart', 'verified_if_auth']);
Route::post('/cart/service/{service}', [\App\Http\Controllers\CartController::class, 'addService'])->name('cart.add-service')->middleware(['throttle:cart', 'verified_if_auth']);
Route::patch('/cart/{cartItem}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update')->middleware(['throttle:cart', 'verified_if_auth']);
Route::delete('/cart/{cartItem}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove')->middleware(['throttle:cart', 'verified_if_auth']);
Route::post('/cart/clear', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear')->middleware(['throttle:cart', 'verified_if_auth']);


Route::middleware(['auth', 'verified_if_auth'])->group(function () {
    Route::get('/checkout', [\App\Http\Controllers\OrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store')->middleware('throttle:orders');
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:profile');
    Route::get('/my-orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::post('/products/{product}/reviews', [\App\Http\Controllers\ReviewController::class, 'storeProduct'])->name('reviews.store.product')->middleware('throttle:reviews');
    Route::post('/services/{service}/reviews', [\App\Http\Controllers\ReviewController::class, 'storeService'])->name('reviews.store.service')->middleware('throttle:reviews');
    Route::post('/news/{news:slug}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store')->middleware('throttle:reviews');
    Route::post('/comments/{comment}/helpful', [\App\Http\Controllers\CommentController::class, 'helpful'])->name('comments.helpful')->middleware('throttle:reviews');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('home')->with('message', 'Email успешно подтверждён.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back();
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Ссылка для подтверждения email отправлена повторно.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});