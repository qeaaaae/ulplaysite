<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Service extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => Category::forgetCategoryCache());
        static::deleted(fn () => Category::forgetCategoryCache());
    }

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'content',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
