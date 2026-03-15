<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'link',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function getImageAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : asset('storage/' . $this->image_path);
    }
}
