<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Image;
use App\Models\News;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
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

        // Миграция существующих данных из image_path / images
        if (Schema::hasTable('products')) {
            Product::whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->chunkById(200, function ($products): void {
                    foreach ($products as $product) {
                        if (! $product->images()->exists()) {
                            $product->images()->create([
                                'path' => $product->image_path,
                                'is_cover' => true,
                                'position' => 0,
                            ]);
                        }
                    }
                });
        }

        if (Schema::hasTable('services')) {
            Service::whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->chunkById(200, function ($services): void {
                    foreach ($services as $service) {
                        if (! $service->images()->exists()) {
                            $service->images()->create([
                                'path' => $service->image_path,
                                'is_cover' => true,
                                'position' => 0,
                            ]);
                        }
                    }
                });
        }

        if (Schema::hasTable('news')) {
            News::whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->chunkById(200, function ($items): void {
                    foreach ($items as $news) {
                        if (! $news->images()->exists()) {
                            $news->images()->create([
                                'path' => $news->image_path,
                                'is_cover' => true,
                                'position' => 0,
                            ]);
                        }
                    }
                });
        }

        if (Schema::hasTable('categories')) {
            Category::whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->chunkById(200, function ($categories): void {
                    foreach ($categories as $category) {
                        if (! $category->images()->exists()) {
                            $category->images()->create([
                                'path' => $category->image_path,
                                'is_cover' => true,
                                'position' => 0,
                            ]);
                        }
                    }
                });
        }

        if (Schema::hasTable('banners')) {
            Banner::whereNotNull('image_path')
                ->where('image_path', '!=', '')
                ->chunkById(200, function ($banners): void {
                    foreach ($banners as $banner) {
                        if (! $banner->images()->exists()) {
                            $banner->images()->create([
                                'path' => $banner->image_path,
                                'is_cover' => true,
                                'position' => 0,
                            ]);
                        }
                    }
                });
        }

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

