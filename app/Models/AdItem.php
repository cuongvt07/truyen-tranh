<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'title',
        'image_path',
        'image_url',
        'link',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function getImageAttribute(): ?string
    {
        if (!empty($this->attributes['image_url'])) {
            return $this->attributes['image_url'];
        }

        if (!empty($this->attributes['image_path'])) {
            return asset('storage/' . $this->attributes['image_path']);
        }

        return null;
    }
}
