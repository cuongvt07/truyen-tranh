<?php

namespace App\Support;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Log;

class PaymentConfig
{
    /** Memo theo request để tránh truy vấn + giải mã lặp lại. */
    private static ?array $paypal = null;

    /**
     * Trả về cấu hình PayPal đang hiệu lực theo mode.
     * Ưu tiên giá trị trong DB (đã giải mã); thiếu thì fallback về .env (config/services).
     */
    public static function paypal(): array
    {
        if (self::$paypal !== null) {
            return self::$paypal;
        }

        $clientId = $secret = $webhookId = null;
        $mode = config('services.paypal.mode', 'sandbox');

        try {
            $ps = PaymentSetting::current();
            $mode = $ps->paypal_mode ?: $mode;
            $prefix = $mode === 'live' ? 'paypal_live_' : 'paypal_sandbox_';
            $clientId  = $ps->{$prefix . 'client_id'} ?: null;
            $secret    = $ps->{$prefix . 'secret'} ?: null;       // tự giải mã qua cast
            $webhookId = $ps->{$prefix . 'webhook_id'} ?: null;   // tự giải mã qua cast
        } catch (\Throwable $e) {
            // Lỗi giải mã (vd APP_KEY đổi) → fallback .env, không lộ secret ra log.
            Log::error('PaymentConfig: failed to read payment_settings, falling back to env', [
                'error' => $e->getMessage(),
            ]);
        }

        return self::$paypal = [
            'mode'       => $mode,
            'client_id'  => $clientId  ?: config('services.paypal.client_id'),
            'secret'     => $secret    ?: config('services.paypal.secret'),
            'webhook_id' => $webhookId ?: config('services.paypal.webhook_id'),
            'base_url'   => $mode === 'live'
                ? 'https://api-m.paypal.com'
                : 'https://api-m.sandbox.paypal.com',
        ];
    }

    /** Xoá memo (gọi sau khi admin lưu cấu hình mới trong cùng request, nếu cần). */
    public static function flush(): void
    {
        self::$paypal = null;
    }
}
