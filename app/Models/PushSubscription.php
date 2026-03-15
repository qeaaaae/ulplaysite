<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toWebPushSubscription(): array
    {
        $arr = ['endpoint' => $this->endpoint];
        if ($this->public_key && $this->auth_token) {
            $arr['keys'] = [
                'p256dh' => $this->public_key,
                'auth' => $this->auth_token,
            ];
        }
        $encoding = $this->content_encoding === 'aesgcm' ? 'aes128gcm' : ($this->content_encoding ?: 'aes128gcm');
        $arr['contentEncoding'] = $encoding;

        return $arr;
    }
}
