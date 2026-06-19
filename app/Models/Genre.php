<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Genre extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['name', 'description', 'cover_image'];

    protected static function booted(): void
    {
        static::saved(function (self $genre) {
            if ($genre->wasRecentlyCreated || $genre->wasChanged('name') || !$genre->slug()->exists()) {
                Slug::ensureFor($genre, 'genre', $genre->name);
            }
            bump_sitemap_version();
        });
        static::deleted(fn () => bump_sitemap_version());
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'articles_genres', 'genre_id', 'article_id');
    }

    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable')->where('type', 'genre');
    }

    public function getRouteKey()
    {
        return $this->relationLoaded('slug')
            ? ($this->slug?->slug ?? $this->getKey())
            : ($this->slug()->value('slug') ?? $this->getKey());
    }
}
