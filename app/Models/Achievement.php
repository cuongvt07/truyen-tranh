<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['key','name','name_en','description','description_en','category','metric','target','reward_credits','sort_order'];
    protected $casts    = ['target' => 'integer', 'reward_credits' => 'integer', 'sort_order' => 'integer'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        return app()->getLocale() === 'en' && $this->description_en ? $this->description_en : ($this->description ?? '');
    }
}
