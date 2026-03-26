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

    public function getPriceAttribute(): float
    {
        $price = $this->product->price;
        if ($this->product->discount_percent) {
            $price *= (100 - $this->product->discount_percent) / 100;
        }

        return (float) $price;
    }

    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getTitleAttribute(): string
    {
        return $this->product->title;
    }
}
