<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;

class Banner extends Model
{
    use HasFactory;

    private const CACHE_KEY_ACTIVE = 'banners.active';
    private const CACHE_TTL = 600;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY_ACTIVE));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY_ACTIVE));
    }

    /** @return Collection<int, Banner> */
    public static function getCachedActive(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_TTL, fn () => self::with('images')->where('active', true)->orderBy('id')->get());
    }

    protected $fillable = [
        'title',
        'description',
        'link',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
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
}
