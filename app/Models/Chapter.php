<?php

namespace App\Models;

use App\Scopes\PublishedChapterScope;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'number', 'article_id', 'credit_cost', 'published_at'];
    protected $perPage = 50;

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Route-model-binding bỏ qua PublishedChapterScope để ADMIN bind được chương hẹn giờ.
     * An toàn vì chỉ route admin bind {chapter}; client đọc chương qua {number} thô
     * (đi qua $article->chapters() nên vẫn bị scope ẩn). KHÔNG thêm route public bind Chapter.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return static::withoutGlobalScope(PublishedChapterScope::class)
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->first();
    }

    /** Chương đang hẹn giờ (chưa tới giờ đăng). */
    public function isScheduled(): bool
    {
        return $this->published_at !== null && $this->published_at->isFuture();
    }

    /**
     * Parse chuỗi ngày/giờ người dùng nhập (có thể copy-paste từ Excel) thành Carbon.
     * Hỗ trợ nhiều định dạng phổ biến; trả null nếu rỗng/không parse được.
     */
    public static function parsePublishedAt(?string $text): ?Carbon
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }
        $formats = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y', 'd-m-Y H:i', 'd-m-Y'];
        foreach ($formats as $f) {
            try {
                $dt = Carbon::createFromFormat($f, $text);
                if ($dt !== false) {
                    if (! str_contains($f, 'H')) {
                        $dt->startOfDay();
                    }
                    return $dt;
                }
            } catch (\Throwable $e) {
                // thử format tiếp theo
            }
        }
        try {
            return Carbon::parse($text);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * BẢO MẬT (chống stored XSS): làm sạch HTML nội dung chương tại 1 điểm duy nhất
     * cho MỌI đường lưu (admin ChapterController, MyArticleController của poster, seed...).
     * Poster là nguồn bán-tin-cậy nên không được để mã JS độc chạy trên trình duyệt
     * người đọc/admin. Xem App\Support\HtmlSanitizer.
     */
    public function setContentAttribute($value): void
    {
        $this->attributes['content'] = HtmlSanitizer::clean($value);
    }

    protected static function booted(): void
    {
        // Ẩn chương hẹn giờ khỏi public (admin/poster opt-out bằng withoutGlobalScope).
        static::addGlobalScope(new PublishedChapterScope());

        // Làm mới sitemap khi chương thêm/sửa/xoá; bỏ qua khi chỉ tăng lượt xem.
        static::saved(function (self $chapter) {
            $ignore = ['view', 'updated_at'];
            if (count(array_diff(array_keys($chapter->getChanges()), $ignore)) > 0) {
                bump_sitemap_version();
            }
        });
        static::deleted(fn () => bump_sitemap_version());
    }

    protected function getViewTextAttribute(): string
    {
        $value = $this->view;
        return $value.' lượt xem';
    }

    protected function getNumberTextAttribute()
    {
        $value = $this->number;
        return 'Chương '.$value;
    }

    protected function getPreviousAttribute()
    {
        $value = $this->number;
        $previousNumber = $value - 1;
        $previousChapter = Chapter::query()->where('article_id', $this->article_id)->where('number', $previousNumber)->first();
        return $previousChapter;
    }

    protected function getNextAttribute()
    {
        $value = $this->number;
        $nextNumber = $value + 1;
        $nextChapter = Chapter::query()->where('article_id', $this->article_id)->where('number', $nextNumber)->first();
        return $nextChapter;
    }
    protected function getCreatedAtTextAttribute()
    {
        return $this->created_at->diffForHumans();
    }
    protected function getUpdatedAtTextAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function article(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }

    public function unlocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChapterUnlock::class, 'chapter_id', 'id');
    }

    /**
     * Returns the credit cost to unlock this chapter.
     * Returns 0 if the chapter is free (below credit_start_chapter or no config).
     */
    public function getEffectiveCreditCost(Article $article): int
    {
        $start = $article->credit_start_chapter;
        if ($start === null || $this->number < $start) {
            return 0;
        }
        // Per-chapter override takes precedence over article default
        if ($this->credit_cost !== null) {
            return (int) $this->credit_cost;
        }
        return (int) $article->credit_per_chapter;
    }

    public function increaseViewCount()
    {
        $this->timestamps = false;
        $this->increment('view');
        $this->save();
        $this->timestamps = true;
    }
}
