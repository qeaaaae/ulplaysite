<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'user_id',
        'rating',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imagesRelation(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('position');
    }

    public function getImageUrlsAttribute(): array
    {
        return $this->imagesRelation
            ->take(3)
            ->map(fn (Image $image): ?string => $image->url)
            ->filter()
            ->values()
            ->all();
    }

    public function publicReviewableUrl(): ?string
    {
        $m = $this->reviewable;
        if ($m instanceof Product) {
            return route('products.show', $m) . '#reviews';
        }
        if ($m instanceof Service) {
            return route('services.show', $m);
        }

        return null;
    }

    public function reviewableDisplayTitle(): string
    {
        return $this->reviewable?->title ?? 'Карточка недоступна';
    }

    public function reviewableKindLabel(): string
    {
        $m = $this->reviewable;
        if ($m instanceof Product) {
            return 'товар';
        }
        if ($m instanceof Service) {
            return 'услуга';
        }

        return '';
    }
}
