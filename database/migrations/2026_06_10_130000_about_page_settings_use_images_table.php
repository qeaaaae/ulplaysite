<?php

declare(strict_types=1);

use App\Models\AboutPageSetting;
use App\Models\Image;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('about_page_settings')) {
            return;
        }

        $about = AboutPageSetting::query()->first();
        if ($about && ! empty($about->image_path) && Schema::hasColumn('about_page_settings', 'image_path')) {
            Image::query()->create([
                'imageable_type' => AboutPageSetting::class,
                'imageable_id' => $about->id,
                'path' => $about->image_path,
                'is_cover' => true,
                'position' => 0,
            ]);
        }

        if (Schema::hasColumn('about_page_settings', 'image_path')) {
            Schema::table('about_page_settings', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('about_page_settings')) {
            return;
        }

        if (! Schema::hasColumn('about_page_settings', 'image_path')) {
            Schema::table('about_page_settings', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('address');
            });
        }

        $about = AboutPageSetting::query()->first();
        if ($about) {
            $cover = $about->images()->where('is_cover', true)->first()
                ?? $about->images()->orderBy('position')->first();
            if ($cover) {
                $about->update(['image_path' => $cover->path]);
            }
            $about->images()->delete();
        }
    }
};
