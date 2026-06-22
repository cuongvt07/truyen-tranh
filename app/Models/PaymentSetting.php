<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $guarded = [];

    /**
     * Secret + webhook_id được mã hoá at-rest bằng APP_KEY (AES-256).
     * Truy cập thuộc tính sẽ tự giải mã; gán giá trị sẽ tự mã hoá.
     * client_id KHÔNG mã hoá vì là giá trị công khai (dùng ở frontend).
     */
    protected $casts = [
        'paypal_sandbox_secret'     => 'encrypted',
        'paypal_sandbox_webhook_id' => 'encrypted',
        'paypal_live_secret'        => 'encrypted',
        'paypal_live_webhook_id'    => 'encrypted',
        'google_client_secret'      => 'encrypted',
    ];

    /** Singleton: luôn dùng 1 bản ghi cấu hình. */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
