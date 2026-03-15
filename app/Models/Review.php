<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'user_id',
        'rating',
        'body',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
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

    public function getImageUrlsAttribute(): array
    {
        $paths = $this->images ?? [];
        return array_map(fn (string $path): string => str_starts_with($path, 'http')
            ? $path
            : asset('storage/' . $path), $paths);
    }
}
