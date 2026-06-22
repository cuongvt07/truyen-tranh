<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Character extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'slug', 'photo', 'type', 'description'];

    /** URL nhân vật dùng slug thay cho id. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        // Sinh slug khi tạo (hoặc khi còn trống). Không đổi slug khi rename để giữ URL ổn định.
        static::saving(function (Character $character) {
            if (blank($character->slug) && filled($character->name)) {
                $character->slug = static::uniqueSlug($character->name);
            }
        });
    }

    /** Slug duy nhất: kebab tên, đụng độ thì thêm hậu tố -2, -3... */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'character';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_character');
    }
}
