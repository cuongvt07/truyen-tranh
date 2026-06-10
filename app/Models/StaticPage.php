<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaticPage extends Model
{
    public const TYPES = [
        'forum'        => 'Forum Root',
        'forum_category' => 'Forum Category',
        'forum_post'   => 'Forum Post',
        'faq'          => 'FAQ Root',
        'faq_category' => 'FAQ Category',
        'faq_article'  => 'FAQ Article',
        'rules'        => 'Rules',
        'custom'       => 'Custom',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        'pending'  => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];

    protected $fillable = [
        'parent_id',
        'user_id',
        'page_type',
        'slug',
        'title_en',
        'title_vi',
        'content_en',
        'content_vi',
        'excerpt_en',
        'excerpt_vi',
        'section_label_en',
        'section_label_vi',
        'comments_enabled',
        'is_pinned',
        'view_count',
        'is_active',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'comments_enabled' => 'boolean',
        'is_pinned'        => 'boolean',
        'view_count'       => 'integer',
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
    ];

    // ------------------------------------------------------------------ scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePublic($query)
    {
        return $query->active()->approved();
    }

    // --------------------------------------------------------------- relations

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(StaticPageComment::class, 'static_page_id')->orderBy('created_at');
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

    public function localizedExcerpt(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $excerpt = $locale === 'vi' ? $this->excerpt_vi : $this->excerpt_en;

        if ($excerpt !== null && trim($excerpt) !== '') {
            return $excerpt;
        }

        return \Illuminate\Support\Str::limit(strip_tags($this->localizedContent($locale)), 180);
    }

    public function localizedSectionLabel(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return ($locale === 'vi' && $this->section_label_vi) ? $this->section_label_vi : (string) $this->section_label_en;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
