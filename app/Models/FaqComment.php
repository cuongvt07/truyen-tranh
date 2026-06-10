<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqComment extends Model
{
    protected $fillable = [
        'article_id',
        'user_id',
        'parent_id',
        'content',
        'score',
        'reply_count',
    ];

    protected $casts = [
        'score' => 'integer',
        'reply_count' => 'integer',
    ];

    // --------------------------------------------------------------- relations

    public function article(): BelongsTo
    {
        return $this->belongsTo(FaqArticle::class, 'article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FaqComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(FaqComment::class, 'parent_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(FaqCommentVote::class, 'comment_id');
    }

    // ----------------------------------------------------------------- helpers

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function canBeDeletedBy(?User $user): bool
    {
        if (!$user) return false;
        if ($user->is_admin) return true;
        return $this->user_id === $user->id;
    }
}
