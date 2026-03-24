<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'news_id',
        'user_id',
        'body',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function isEditableBy(?\App\Models\User $user): bool
    {
        if (! $user || $this->user_id === null) {
            return false;
        }

        return (int) $this->user_id === (int) $user->getKey();
    }

    public function isDeletableBy(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->is_admin || ($this->user_id !== null && (int) $this->user_id === (int) $user->getKey());
    }

    public function helpfulVotes(): HasMany
    {
        return $this->hasMany(CommentHelpfulVote::class, 'comment_id');
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
