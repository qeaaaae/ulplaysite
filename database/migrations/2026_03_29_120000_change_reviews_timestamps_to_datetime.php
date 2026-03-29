<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Для баз, созданных до перехода create_reviews на dateTime: меняем TIMESTAMP → DATETIME.
 * Повторный запуск безопасен (проверка information_schema).
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true) || ! Schema::hasTable('reviews')) {
            return;
        }

        $db = Schema::getConnection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$db, 'reviews', 'created_at']
        );

        if ($row === null || strtolower((string) $row->DATA_TYPE) === 'datetime') {
            return;
        }

        DB::statement('ALTER TABLE `reviews` MODIFY `created_at` DATETIME NULL, MODIFY `updated_at` DATETIME NULL');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true) || ! Schema::hasTable('reviews')) {
            return;
        }

        $db = Schema::getConnection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$db, 'reviews', 'created_at']
        );

        if ($row === null || strtolower((string) $row->DATA_TYPE) !== 'datetime') {
            return;
        }

        DB::statement('ALTER TABLE `reviews` MODIFY `created_at` TIMESTAMP NULL, MODIFY `updated_at` TIMESTAMP NULL');
    }
};
