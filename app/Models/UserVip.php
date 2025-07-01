<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVip extends Model
{
    protected $fillable = [
        'user_id',
        'package_name',
        'package_days',
        'package_coins',
        'start_at',
        'end_at',
    ];

    protected $dates = [
        'start_at',
        'end_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
