<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    private const CACHE_KEY_ALL = 'categories.ordered';
    private const CACHE_KEY_ROOTS = 'categories.roots';
    private const CACHE_KEY_WITH_COUNT = 'categories.with_products_count';
    private const CACHE_TTL_ALL = 600;
    private const CACHE_TTL_ROOTS = 600;
    private const CACHE_TTL_WITH_COUNT = 300;

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCategoryCache());
        static::deleted(fn () => self::forgetCategoryCache());
    }

    public static function forgetCategoryCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
        Cache::forget(self::CACHE_KEY_ROOTS);
        Cache::forget(self::CACHE_KEY_WITH_COUNT);
    }

    public static function forgetWithProductsCountCache(): void
    {
        Cache::forget(self::CACHE_KEY_WITH_COUNT);
    }

    /** @return Collection<int, Category> */
    public static function getCachedAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL_ALL, fn () => self::orderBy('name')->get());
    }

    /** @return Collection<int, Category> */
    public static function getCachedRoots(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ROOTS, self::CACHE_TTL_ROOTS, fn () => self::whereNull('parent_id')->orderBy('name')->get());
    }

    /** @return Collection<int, Category> */
    public static function getCachedWithProductsCount(): Collection
    {
        return Cache::remember(self::CACHE_KEY_WITH_COUNT, self::CACHE_TTL_WITH_COUNT, fn () => self::withCount('products')->orderBy('name')->get());
    }

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('position');
    }

    public function getImageAttribute(): ?string
    {
        $image = $this->images->firstWhere('is_cover', true) ?? $this->images->first();
        return $image?->url;
    }

    public function getCountAttribute(): int
    {
        return (int) ($this->attributes['products_count'] ?? 0);
    }
}
