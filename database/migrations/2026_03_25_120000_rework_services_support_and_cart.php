<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->longText('content')->nullable()->after('description');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['price', 'type']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        DB::table('cart_items')->whereNotNull('service_id')->delete();

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'content']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable();
            $table->string('type')->default('repair');
        });
    }
};
