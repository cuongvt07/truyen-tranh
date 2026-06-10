<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqCategory extends Model
{
    protected $fillable = [
        'slug',
        'title_en',
        'title_vi',
        'description_en',
        'description_vi',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ------------------------------------------------------------------ scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // --------------------------------------------------------------- relations

    public function articles(): HasMany
    {
        return $this->hasMany(FaqArticle::class, 'category_id');
    }

    // ----------------------------------------------------------------- helpers

    public function localizedTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return ($locale === 'vi' && $this->title_vi) ? $this->title_vi : $this->title_en;
    }

    public function localizedDescription(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $desc = $locale === 'vi' ? $this->description_vi : $this->description_en;
        return ($desc === null || trim($desc) === '') ? (string) $this->description_en : $desc;
    }
}
