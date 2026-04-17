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
            // Для старых MySQL/InnoDB с лимитом ключа 1000 bytes используем 191 символ:
            // 191 * 4 = 764 bytes (utf8mb4), что безопасно для unique index.
            $table->string('source_url', 191)->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table): void {
            $table->dropColumn('source_url');
        });
    }
};
