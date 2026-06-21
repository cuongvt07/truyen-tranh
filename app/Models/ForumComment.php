<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumComment extends Model
{
    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'content', 'score', 'reply_count', 'is_hidden',
    ];

    protected $casts = [
        'score' => 'integer',
        'reply_count' => 'integer',
        'is_hidden' => 'boolean',
    ];

    // --------------------------------------------------------------- relations

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'parent_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ForumCommentVote::class, 'comment_id');
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
