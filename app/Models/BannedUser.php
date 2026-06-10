<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannedUser extends Model
{
    use HasFactory;
    protected $primaryKey = "user_id";

    protected function getRemainingDaysAttribute($value)
    {
        if (!$this->expired_at) {
            return 'Vĩnh viễn';
        }

        $expiredDate = Carbon::parse($this->expired_at);
        if ($expiredDate->isPast()) {
            return 'Đã hết hạn';
        }

        $remainingDays = Carbon::now()->diffInDays($expiredDate);

        return $remainingDays . ' ngày';
    }
    protected function getCreatedAtTextAttribute()
    {
        return $this->created_at->diffForHumans();
    }
    protected function getUpdatedAtTextAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }
}
