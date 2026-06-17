<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name', 'name_en', 'sort_order'];

    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'en' && $this->name_en
            ? $this->name_en
            : $this->name;
    }
}
