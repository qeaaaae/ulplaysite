<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            // Для MySQL + utf8mb4 уникальный индекс на 1024 символа превышает лимит длины ключа.
            $table->string('source_url', 512)->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->dropColumn('source_url');
        });
    }
};
