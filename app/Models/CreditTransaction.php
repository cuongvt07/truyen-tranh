<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'balance_after', 'type', 'description',
        'reference_type', 'reference_id', 'admin_id',
    ];

    protected $casts = [
        'amount'        => 'integer',
        'balance_after' => 'integer',
    ];

    /** Các nguồn biến động credit. Nhãn dịch ở messages.credit_log.types.* */
    public const TYPES = [
        'purchase'           => 'purchase',            // nạp xu (PayPal/SePay) cộng vào
        'daily_subscription' => 'daily_subscription',  // credit hằng ngày của gói subscription
        'subscription_init'  => 'subscription_init',   // credit ngày đầu khi mua subscription
        'chapter_unlock'     => 'chapter_unlock',      // trừ khi mở khoá chương
        'vip_purchase'       => 'vip_purchase',        // trừ khi mua VIP bằng xu
        'signup_bonus'       => 'signup_bonus',        // thưởng đăng ký Google
        'achievement'        => 'achievement',         // thưởng thành tựu
        'admin_adjust'       => 'admin_adjust',        // admin chỉnh tay
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /** Nhãn nguồn đã dịch theo locale hiện tại. */
    public function typeLabel(): string
    {
        return __('messages.credit_log.types.' . $this->type);
    }
}
