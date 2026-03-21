<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'path',
        'is_cover',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): ?string
    {
        if (empty($this->path)) {
            return null;
        }

        return str_starts_with($this->path, 'http')
            ? $this->path
            : Storage::disk('public')->url($this->path);
    }
}

