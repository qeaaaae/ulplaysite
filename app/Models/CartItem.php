<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'service_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getPriceAttribute(): float
    {
        if ($this->product_id) {
            $price = $this->product->price;
            if ($this->product->discount_percent) {
                $price *= (100 - $this->product->discount_percent) / 100;
            }
            return (float) $price;
        }
        return (float) ($this->service->price ?? 0);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getTitleAttribute(): string
    {
        return $this->product_id
            ? $this->product->title
            : $this->service->title;
    }
}
