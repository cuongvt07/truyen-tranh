<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingHistory extends Model
{
    protected $fillable = [
        'user_id',
        'article_id',
        'chapter_id',
        'chapter_number',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function article() { return $this->belongsTo(Article::class); }
    public function chapter() { return $this->belongsTo(Chapter::class); }

    // Ghi lại lịch sử đọc — upsert để không tạo duplicate
    public static function record(int $userId, int $articleId, int $chapterId, int $chapterNumber): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'chapter_id' => $chapterId],
            ['article_id' => $articleId, 'chapter_number' => $chapterNumber, 'read_at' => now()]
        );
    }

    // Lấy danh sách "đang đọc" — 1 article = chapter mới nhất
    public static function continueReading(int $userId, int $limit = 20)
    {
        return static::where('user_id', $userId)
            ->with(['article.slug', 'chapter'])
            ->orderByDesc('read_at')
            ->get()
            ->unique('article_id')
            ->take($limit)
            ->values();
    }
}
