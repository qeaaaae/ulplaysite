<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_tickets') && ! Schema::hasColumn('support_tickets', 'type')) {
            Schema::table('support_tickets', function (Blueprint $table): void {
                $table->string('type', 50)->default('technical_issue')->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets') && Schema::hasColumn('support_tickets', 'type')) {
            Schema::table('support_tickets', function (Blueprint $table): void {
                $table->dropColumn('type');
            });
        }
    }
};

