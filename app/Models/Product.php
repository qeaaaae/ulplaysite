<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Avito\AvitoCachedListingUrlLookup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;
    private const DEFAULT_PRODUCT_IMAGE = 'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize';

    protected static function booted(): void
    {
        static::saved(fn () => Category::forgetWithProductsCountCache());
        static::deleted(fn () => Category::forgetWithProductsCountCache());
    }

    protected $fillable = [
        'title',
        'slug',
        'avito_item_id',
        'avito_url',
        'description',
        'video_url',
        'price',
        'category_id',
        'in_stock',
        'stock',
        'discount_percent',
        'is_new',
        'is_recommended',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'in_stock' => 'boolean',
            'stock' => 'integer',
            'is_new' => 'boolean',
            'is_recommended' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('position');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function getImageAttribute(): ?string
    {
        $image = $this->images->firstWhere('is_cover', true) ?? $this->images->first();
        if ($image?->url !== null && $image->url !== '') {
            return $image->url;
        }

        return self::DEFAULT_PRODUCT_IMAGE;
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        return app(\App\Services\VideoEmbedService::class)->toEmbedUrl($this->video_url);
    }

    public function getResolvedAvitoUrlAttribute(): ?string
    {
        return self::resolveAvitoUrlForDisplay($this->avito_url, $this->avito_item_id);
    }

    public static function resolveAvitoUrlForDisplay(?string $avitoUrl, ?string $avitoItemId = null): ?string
    {
        $u = is_string($avitoUrl) ? trim($avitoUrl) : '';
        if ($u !== '') {
            if (str_starts_with($u, 'http://')) {
                $u = 'https://' . substr($u, 7);
            }
            if (str_contains(strtolower($u), 'avito.ru')) {
                return $u;
            }
        }

        $itemId = is_string($avitoItemId) ? trim($avitoItemId) : '';
        if ($itemId === '') {
            return null;
        }

        try {
            // На витрине не ходим в Avito API во время рендера страницы.
            return app(AvitoCachedListingUrlLookup::class)->resolveByItemIdFromCacheOnly($itemId);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function normalizeAvitoPublicUrl(string $url): string
    {
        $url = trim($url);
        if (str_starts_with($url, 'http://')) {
            $url = 'https://' . substr($url, 7);
        }

        return $url;
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('is_new', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('in_stock', true);
    }

    public function scopeRecommended(Builder $query): Builder
    {
        return $query->where('is_recommended', true);
    }
}
