<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class Author extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'description'];

    protected static function booted(): void
    {
        static::saved(fn () => bump_sitemap_version());
        static::deleted(fn () => bump_sitemap_version());
    }

    public function articles(
    ): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'articles_authors',
            'author_id', 'article_id');
    }
}
