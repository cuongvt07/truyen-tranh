<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCheckin extends Model
{
    protected $fillable = [
        'user_id',
        'checkin_date',
        'day_of_month',
        'amount',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'day_of_month' => 'integer',
        'amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
