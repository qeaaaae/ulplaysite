<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_admin',
        'is_blocked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function hasPurchasedProduct(\App\Models\Product $product): bool
    {
        return OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $this->id)->whereIn('status', ['paid', 'processing', 'shipped', 'completed']))
            ->exists();
    }

    public function hasPurchasedService(\App\Models\Service $service): bool
    {
        return OrderItem::query()
            ->where('service_id', $service->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $this->id)->whereIn('status', ['paid', 'processing', 'shipped', 'completed']))
            ->exists();
    }

    /** @return \Illuminate\Support\Collection<int, array{type: 'product'|'service', model: Product|Service}> */
    public function getPurchasedWithoutReview(): \Illuminate\Support\Collection
    {
        $orderItemIds = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $this->id)->whereIn('status', ['paid', 'processing', 'shipped', 'completed']))
            ->get();

        $productIds = $orderItemIds->pluck('product_id')->filter()->unique()->values()->all();
        $serviceIds = $orderItemIds->pluck('service_id')->filter()->unique()->values()->all();

        $reviewedProductIds = Review::where('user_id', $this->id)->where('reviewable_type', Product::class)->pluck('reviewable_id')->all();
        $reviewedServiceIds = Review::where('user_id', $this->id)->where('reviewable_type', Service::class)->pluck('reviewable_id')->all();

        $productIds = array_diff($productIds, $reviewedProductIds);
        $serviceIds = array_diff($serviceIds, $reviewedServiceIds);

        $items = collect();
        if ($productIds !== []) {
            foreach (Product::whereIn('id', $productIds)->get() as $product) {
                $items->push(['type' => 'product', 'model' => $product]);
            }
        }
        if ($serviceIds !== []) {
            foreach (Service::whereIn('id', $serviceIds)->get() as $service) {
                $items->push(['type' => 'service', 'model' => $service]);
            }
        }

        return $items;
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = $this->freshTimestamp();

        return (bool) $this->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \Illuminate\Auth\Notifications\VerifyEmail());
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }
}
