<?php

use App\Models\Image;
use App\Models\Review;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable');
            $table->string('path');
            $table->boolean('is_cover')->default(false);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'images')) {
            Review::whereNotNull('images')
                ->chunkById(200, function ($reviews): void {
                    foreach ($reviews as $review) {
                        $paths = (array) ($review->images ?? []);
                        foreach (array_values($paths) as $index => $path) {
                            if ($index >= 3) {
                                break;
                            }

                            $review->imagesRelation()->firstOrCreate([
                                'path' => $path,
                                'position' => $index,
                            ], [
                                'is_cover' => $index === 0,
                            ]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};

