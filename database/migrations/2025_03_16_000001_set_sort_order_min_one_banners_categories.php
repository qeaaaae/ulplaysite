<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('banners')->where('sort_order', 0)->update(['sort_order' => 1]);
        DB::table('categories')->where('sort_order', 0)->update(['sort_order' => 1]);
    }

    public function down(): void
    {
    }
};
