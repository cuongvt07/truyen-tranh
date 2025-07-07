<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class AffiliateLink extends Model
{
    use HasFactory;

    protected $fillable = ['article_id', 'link', 'image_path'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
