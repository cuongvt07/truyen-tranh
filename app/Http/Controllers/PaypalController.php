<?php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use App\Models\Deposit;
use App\Services\PackageBenefitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    private $baseUrl;

    public function __construct()
    {
        $mode = config('services.paypal.mode', 'sandbox');
        $this->baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    // ─── Trang checkout ─────────────────────────────────────────────────────
    public function checkout(CreditPackage $creditPackage)
    {
        abort_if(!$creditPackage->is_active, 404);
        abort_if($creditPackage->price_usd <= 0, 404, 'Gói này chưa hỗ trợ thanh toán USD.');

        return view('client.checkout.index', [
            'package'        => $creditPackage,
            'paypalClientId' => config('services.paypal.client_id'),
        ]);
    }

    // ─── Tạo order PayPal (gọi từ JS) ───────────────────────────────────────
    public function createOrder(Request $request)
    {
        $request->validate(['package_id' => 'required|exists:credit_packages,id']);

        $pkg   = CreditPackage::findOrFail($request->package_id);
        $token = $this->getAccessToken();
        $description = $pkg->isSubscription()
            ? "Mua subscription {$pkg->subscription_days} ngay - {$pkg->name}"
            : "Mua {$pkg->coins} xu - {$pkg->name}";

        $res = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => 'pkg_' . $pkg->id,
                    'description'  => $description,
                    'amount'       => [
                        'currency_code' => 'USD',
                        'value'         => number_format($pkg->price_usd, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name'          => config('app.name'),
                    'landing_page'        => 'NO_PREFERENCE',
                    'user_action'         => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                ],
            ]);

        if ($res->failed()) {
            Log::error('PayPal createOrder failed', $res->json());
            return response()->json(['error' => 'Không thể tạo đơn PayPal.'], 500);
        }

        return response()->json(['id' => $res->json('id')]);
    }

    // ─── Capture order PayPal (gọi từ JS sau khi user approve) ─────────────
    public function captureOrder(Request $request)
    {
        $request->validate([
            'order_id'   => 'required|string',
            'package_id' => 'required|exists:credit_packages,id',
        ]);

        $pkg   = CreditPackage::findOrFail($request->package_id);
        $token = $this->getAccessToken();

        $res = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody('{}', 'application/json')
            ->post("{$this->baseUrl}/v2/checkout/orders/{$request->order_id}/capture");

        if ($res->failed()) {
            Log::error('PayPal capture failed', $res->json());
            return response()->json(['error' => 'Thanh toán thất bại.'], 500);
        }

        $data   = $res->json();
        $status = $data['status'] ?? '';

        if ($status !== 'COMPLETED') {
            return response()->json(['error' => 'Giao dịch chưa hoàn thành: ' . $status], 422);
        }

        $captureId = $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? $request->order_id;

        // Tránh xử lý trùng
        if (Deposit::where('transaction_id', $captureId)->exists()) {
            return response()->json(['success' => true, 'message' => 'Đã xử lý trước đó.']);
        }

        // Lưu giao dịch
        Deposit::create([
            'user_id'           => auth()->id(),
            'amount'            => $pkg->price_usd,
            'payment_method'    => 'paypal',
            'transaction_id'    => $captureId,
            'payment_reference' => $request->order_id,
            'status'            => 'completed',
            'content'           => $pkg->isSubscription()
                ? "PayPal: {$pkg->name} (subscription {$pkg->subscription_days} days, {$pkg->daily_credits} credits/day)"
                : "PayPal: {$pkg->name} ({$pkg->coins} xu)",
        ]);

        // Cộng xu cho user
        $benefit = app(PackageBenefitService::class)->grant(auth()->user(), $pkg);

        if ($pkg->isSubscription()) {
            return response()->json([
                'success' => true,
                'type'    => 'subscription',
                'message' => "Thanh toán thành công! Subscription đã kích hoạt đến " . $benefit['vip_end']->format('d/m/Y') . ". Bạn đã nhận " . number_format($benefit['initial_credits']) . " credit ngày đầu.",
            ]);
        }

        return response()->json([
            'success' => true,
            'type'    => 'credit',
            'coins'   => $pkg->coins,
            'message' => "Thanh toán thành công! Bạn nhận được {$pkg->coins} xu.",
        ]);
    }

    // ─── PayPal Webhook (server-side xác nhận async) ────────────────────────
    public function webhook(Request $request)
    {
        // Verify chữ ký từ PayPal
        if (!$this->verifyWebhookSignature($request)) {
            Log::warning('PayPal webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event    = $request->json()->all();
        $type     = $event['event_type'] ?? '';
        $resource = $event['resource'] ?? [];

        Log::info('PayPal webhook received', ['type' => $type]);

        if ($type === 'PAYMENT.CAPTURE.COMPLETED') {
            $captureId = $resource['id'] ?? null;
            $amount    = (float) ($resource['amount']['value'] ?? 0);
            $orderId   = $resource['supplementary_data']['related_ids']['order_id']
                         ?? ($resource['links'][0]['href'] ?? '');

            if (!$captureId) {
                return response()->json(['ok' => true]);
            }

            // Nếu đã xử lý (client-side capture đã ghi) → bỏ qua
            if (Deposit::where('transaction_id', $captureId)->where('status', 'completed')->exists()) {
                return response()->json(['ok' => true]);
            }

            // Tìm deposit pending theo payment_reference (order ID) hoặc tạo mới
            $deposit = Deposit::where('payment_reference', $orderId)->first();

            if ($deposit && $deposit->status !== 'completed') {
                // Cộng xu cho user nếu chưa cộng
                if ($deposit->user_id) {
                    // Đọc số coins từ content "PayPal: Gói X (YYYY xu)"
                    preg_match('/\((\d+) xu\)/', $deposit->content ?? '', $m);
                    $coins = (int) ($m[1] ?? 0);
                    if ($coins > 0) {
                        \App\Models\User::where('id', $deposit->user_id)->increment('points', $coins);
                    }
                }
                $deposit->update(['status' => 'completed', 'transaction_id' => $captureId]);
                Log::info('PayPal webhook: deposit completed via webhook', ['deposit_id' => $deposit->id]);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function verifyWebhookSignature(Request $request): bool
    {
        $webhookId = config('services.paypal.webhook_id');
        if (!$webhookId) {
            // Nếu chưa cấu hình webhook_id → bỏ qua verify (dev mode)
            Log::warning('PayPal webhook_id not configured, skipping signature verify');
            return true;
        }

        try {
            $token = $this->getAccessToken();
            $res   = Http::withToken($token)->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
                'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url'          => $request->header('PAYPAL-CERT-URL'),
                'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id'        => $webhookId,
                'webhook_event'     => $request->json()->all(),
            ]);
            return ($res->json('verification_status') === 'SUCCESS');
        } catch (\Throwable $e) {
            Log::error('PayPal webhook verify error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // ─── Lấy access token PayPal ─────────────────────────────────────────────
    private function getAccessToken(): string
    {
        $res = Http::asForm()
            ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))
            ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if ($res->failed()) {
            throw new \RuntimeException('Không lấy được PayPal access token.');
        }

        return $res->json('access_token');
    }
}
