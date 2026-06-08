<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'number', 'article_id', 'credit_cost'];
    protected $perPage = 50;

    protected static function booted(): void
    {
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
