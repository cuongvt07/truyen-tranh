<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'photo', 'description',
        'site', 'donation_text', 'donation_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
