<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name', 'description'];

    protected static function booted(): void
    {
        static::saved(fn () => bump_sitemap_version());
        static::deleted(fn () => bump_sitemap_version());
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'articles_genres', 'genre_id', 'article_id');
    }
}
