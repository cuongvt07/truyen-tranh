<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    protected $fillable = [
        'category_id',
        'user_id',
        'slug',
        'title_en',
        'title_vi',
        'content_en',
        'content_vi',
        'status',
        'is_pinned',
        'is_locked',
        'view_count',
        'comment_count',
        'is_active',
    ];

    protected $casts = [
        'is_pinned'  => 'boolean',
        'is_locked'  => 'boolean',
        'is_active'  => 'boolean',
        'view_count' => 'integer',
        'comment_count' => 'integer',
    ];

    // ------------------------------------------------------------------ scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    // --------------------------------------------------------------- relations

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'post_id');
    }

    // ----------------------------------------------------------------- helpers

    public function localizedTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return ($locale === 'vi' && $this->title_vi) ? $this->title_vi : $this->title_en;
    }

    public function localizedContent(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $content = $locale === 'vi' ? $this->content_vi : $this->content_en;
        return ($content === null || trim($content) === '') ? (string) $this->content_en : $content;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeEditedBy(?User $user): bool
    {
        if (!$user) return false;
        if ($user->is_admin) return true;
        return $this->user_id === $user->id;
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
