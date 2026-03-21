<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['products', 'services', 'news', 'categories', 'banners'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'image_path')) {
                Schema::table($table, function (Blueprint $table): void {
                    $table->dropColumn('image_path');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'image_path')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->string('image_path')->nullable()->after('category_id');
            });
        }

        if (Schema::hasTable('services') && ! Schema::hasColumn('services', 'image_path')) {
            Schema::table('services', function (Blueprint $table): void {
                $table->string('image_path')->nullable()->after('price');
            });
        }

        if (Schema::hasTable('news') && ! Schema::hasColumn('news', 'image_path')) {
            Schema::table('news', function (Blueprint $table): void {
                $table->string('image_path')->nullable()->after('content');
            });
        }

        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'image_path')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->string('image_path')->nullable()->after('parent_id');
            });
        }

        if (Schema::hasTable('banners') && ! Schema::hasColumn('banners', 'image_path')) {
            Schema::table('banners', function (Blueprint $table): void {
                $table->string('image_path')->nullable()->after('description');
            });
        }
    }
};

