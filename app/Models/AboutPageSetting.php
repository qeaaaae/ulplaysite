<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AboutPageSetting extends Model
{
    protected $fillable = [
        'address',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'address' => (string) config('site.footer.company.address', ''),
        ]);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')->orderBy('position');
    }
}
