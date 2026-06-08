<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterUnlock extends Model
{
    protected $fillable = ['user_id', 'chapter_id', 'article_id', 'credits_spent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public static function hasUnlocked(int $userId, int $chapterId): bool
    {
        return self::where('user_id', $userId)->where('chapter_id', $chapterId)->exists();
    }
}
