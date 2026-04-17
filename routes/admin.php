<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin', 'throttle:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.products.index'))->name('index');

    Route::get('statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('statistics.index');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
    Route::post('tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::post('push-subscription', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'store'])->name('push-subscription.store');
    Route::post('push-subscription/test', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'test'])->name('push-subscription.test');

    Route::post('upload/image', [\App\Http\Controllers\Admin\UploadController::class, 'image'])->name('upload.image');

    Route::post('products/import-xlsx', [ProductController::class, 'importXlsx'])->name('products.import-xlsx');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('services', ServiceController::class);
    Route::post('news/parse-url', [NewsController::class, 'parseUrl'])->name('news.parse-url');
    Route::resource('news', NewsController::class);
    Route::resource('banners', BannerController::class);

    Route::resource('users', UserController::class)->except(['show']);
    Route::post('users/{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::post('users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
});
