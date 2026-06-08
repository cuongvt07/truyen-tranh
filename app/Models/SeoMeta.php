<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $fillable = [
        'title', 'description', 'canonical_url', 'og_image',
        'noindex', 'nofollow', 'focus_keyword', 'seo_score',
    ];

    protected $casts = [
        'noindex'  => 'boolean',
        'nofollow' => 'boolean',
    ];

    public function seoable()
    {
        return $this->morphTo();
    }
}
