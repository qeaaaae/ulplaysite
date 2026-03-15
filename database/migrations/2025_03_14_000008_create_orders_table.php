<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('new'); // new, paid, processing, shipped, completed, cancelled
            $table->decimal('total', 12, 2);
            $table->json('contact_info')->nullable(); // name, phone, email
            $table->json('delivery_info')->nullable(); // address, type
            $table->json('payment_info')->nullable(); // method
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
