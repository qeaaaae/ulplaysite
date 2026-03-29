<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->json('images')->nullable();
            // DATETIME: без конвертации TZ/DST как у TIMESTAMP в MySQL (иначе 1292 в ночь перехода на летнее время).
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
