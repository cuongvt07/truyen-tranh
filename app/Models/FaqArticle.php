<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqArticle extends Model
{
    protected $fillable = [
        'category_id',
        'slug',
        'title_en',
        'title_vi',
        'content_en',
        'content_vi',
        'is_pinned',
        'view_count',
        'comment_count',
        'comments_enabled',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_pinned'        => 'boolean',
        'comments_enabled' => 'boolean',
        'is_active'        => 'boolean',
        'view_count'       => 'integer',
        'comment_count'    => 'integer',
        'sort_order'       => 'integer',
    ];

    // ------------------------------------------------------------------ scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    // --------------------------------------------------------------- relations

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FaqComment::class, 'article_id');
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

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
