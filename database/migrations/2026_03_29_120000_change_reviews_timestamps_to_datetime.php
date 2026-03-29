<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

/**
 * Раньше здесь был ALTER TIMESTAMP → DATETIME из‑за кастомных дат в ReviewSeeder.
 * Сидер больше не задаёт created_at/updated_at; миграция оставлена пустой, чтобы не ломать
 * историю у тех, у кого она уже выполнена.
 */
return new class extends Migration
{
    public function up(): void {}

    public function down(): void {}
};
