<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Support\PaymentConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentSettingController extends Controller
{
    public function index()
    {
        $ps = PaymentSetting::current();

        // CHỈ truyền client_id (công khai) + cờ "đã cấu hình" cho secret/webhook.
        // Tuyệt đối KHÔNG đưa giá trị secret/webhook thật ra view.
        return view('admin.payment-settings.index', [
            'mode' => $ps->paypal_mode ?: 'sandbox',
            'sandboxClientId' => $ps->paypal_sandbox_client_id,
            'liveClientId' => $ps->paypal_live_client_id,
            'googleClientId' => $ps->google_client_id,
            'flags' => [
                'sandbox_secret'  => filled($ps->paypal_sandbox_secret),
                'sandbox_webhook' => filled($ps->paypal_sandbox_webhook_id),
                'live_secret'     => filled($ps->paypal_live_secret),
                'live_webhook'    => filled($ps->paypal_live_webhook_id),
                'google_secret'   => filled($ps->google_client_secret),
            ],
            'envFallback' => [
                'client_id'  => (bool) config('services.paypal.client_id'),
                'secret'     => (bool) config('services.paypal.secret'),
                'webhook_id' => (bool) config('services.paypal.webhook_id'),
                'google_client_id'     => (bool) config('services.google.client_id'),
                'google_client_secret' => (bool) config('services.google.client_secret'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'paypal_mode' => ['required', 'in:sandbox,live'],
            'paypal_sandbox_client_id' => ['nullable', 'string', 'max:255'],
            'paypal_live_client_id' => ['nullable', 'string', 'max:255'],
            'paypal_sandbox_secret' => ['nullable', 'string', 'max:255'],
            'paypal_sandbox_webhook_id' => ['nullable', 'string', 'max:255'],
            'paypal_live_secret' => ['nullable', 'string', 'max:255'],
            'paypal_live_webhook_id' => ['nullable', 'string', 'max:255'],
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $ps = PaymentSetting::current();
        $ps->paypal_mode = $data['paypal_mode'];
        $ps->paypal_sandbox_client_id = $data['paypal_sandbox_client_id'] ?? null;
        $ps->paypal_live_client_id = $data['paypal_live_client_id'] ?? null;
        $ps->google_client_id = $data['google_client_id'] ?? null;

        // Secret/webhook: chỉ ghi đè khi admin nhập giá trị mới; bỏ trống = giữ nguyên.
        $changed = ['mode' => $data['paypal_mode']];
        foreach (['paypal_sandbox_secret', 'paypal_sandbox_webhook_id', 'paypal_live_secret', 'paypal_live_webhook_id', 'google_client_secret'] as $field) {
            if ($request->filled($field)) {
                $ps->{$field} = $request->input($field);   // tự mã hoá qua cast
                $changed[] = $field;
            }
        }

        $ps->save();
        PaymentConfig::flush();

        // Audit: ai sửa + lúc nào + trường nào (KHÔNG ghi giá trị secret).
        Log::info('Payment settings updated', [
            'by'      => Auth::user()->email ?? Auth::id(),
            'mode'    => $data['paypal_mode'],
            'changed' => $changed,
            'ip'      => $request->ip(),
        ]);

        return back()->with('success', __('messages.flash.payment_saved'));
    }
}
