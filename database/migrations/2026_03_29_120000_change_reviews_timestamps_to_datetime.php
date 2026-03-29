<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TIMESTAMP в MySQL переводит значение в UTC с учётом time_zone сессии; в ночь перехода на летнее время
 * часть «стеночных» времён недопустима → SQLSTATE 22007 / 1292. DATETIME хранит строку без такой конвертации.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE `reviews` MODIFY `created_at` DATETIME NULL, MODIFY `updated_at` DATETIME NULL');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE `reviews` MODIFY `created_at` TIMESTAMP NULL, MODIFY `updated_at` TIMESTAMP NULL');
    }
};
