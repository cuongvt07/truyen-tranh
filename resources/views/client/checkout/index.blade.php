@extends('layout.novelight')

@section('template_title', __('messages.pay.checkout'))

@section('content')
<div class="alpha-workspace">
    <div class="container">
        <section class="alpha-workspace-hero">
            <small>Secure payment</small>
            <h1>{{ __('messages.pay.checkout') }}</h1>
            <p>Review your package and complete payment through PayPal.</p>
        </section>

        <div class="alpha-checkout-shell">
            <section class="alpha-panel alpha-panel--pad" style="margin-bottom:18px">
                <h2 class="alpha-section-title">{{ __('messages.pay.order_summary') }}</h2>
                <div class="alpha-order-item">
                    @if($package->icon)
                        <img src="{{ asset($package->icon) }}" alt="{{ $package->name }}">
                    @else
                        <div class="alpha-order-icon"><i class="fa fa-coins"></i></div>
                    @endif
                    <div>
                        <strong>{{ $package->name }}</strong>
                        @if($package->isSubscription())
                            <span>{{ __('messages.pay.subscription_summary', ['days' => $package->subscription_days, 'credits' => number_format($package->daily_credits)]) }}</span>
                        @else
                            <span>{{ __('messages.pay.credit_package_summary', ['credits' => number_format($package->coins), 'price' => number_format($package->price_usd, 0)]) }}</span>
                        @endif
                    </div>
                    <div class="alpha-order-price">${{ number_format($package->price_usd, 2) }}</div>
                </div>
            </section>

            <section class="alpha-panel alpha-panel--pad" id="payment-card">
                <h2 class="alpha-section-title">{{ __('messages.pay.payment_methods') }}</h2>
                <div class="alpha-payment-method">
                    <div class="alpha-order-icon" style="width:64px;height:46px;font-size:16px;font-weight:900">PayPal</div>
                    <strong>{{ __('messages.pay.paypal_card') }}</strong>
                </div>
                <div id="paypal-button-container" style="margin-top:16px;min-height:48px"></div>
                <div id="payment-status" class="meta-color" style="margin-top:10px">{{ __('messages.pay.loading_paypal') }}</div>
                <p class="meta-color" style="margin-top:12px;text-align:center;font-size:13px">
                    <i class="fa fa-lock"></i> {{ __('messages.pay.secure_paypal_notice') }}
                </p>
            </section>

            <section class="alpha-panel alpha-panel--pad" id="checkout-success" style="display:none;text-align:center;margin-top:18px">
                <div class="alpha-order-icon" style="margin:0 auto 14px"><i class="fa fa-check"></i></div>
                <h2>{{ __('messages.pay.payment_success_title') }}</h2>
                <p id="success-msg" class="meta-color"></p>
                <div class="alpha-form-actions" style="justify-content:center">
                    <a href="{{ route_path('users.transactions', auth()->id()) }}" class="alpha-btn alpha-btn--primary">{{ __('messages.pay.view_transactions') }}</a>
                    <a href="{{ route_path('pages.pricing') }}" class="alpha-btn">{{ __('messages.pay.buy_more') }}</a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@section('page_js')
@if($paypalClientId)
<script src="https://www.paypal.com/sdk/js?client-id={{ urlencode($paypalClientId) }}&currency=USD" crossorigin="anonymous"></script>
<script>
(function () {
    const PACKAGE_ID = {{ $package->id }};
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const SUCCESS_URL = @json(route_path('purchase.success'));
    const FAIL_URL = @json(route_path('purchase.fail'));
    const statusEl = document.getElementById('payment-status');

    function setPaymentError(message) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.style.color = '#dc2626';
    }

    if (!window.paypal || !paypal.Buttons) {
        setPaymentError(@json(__('messages.pay.paypal_load_error')));
        return;
    }

    paypal.Buttons({
        style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'pay', height: 48 },
        createOrder: function () {
            return fetch('/paypal/create-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ package_id: PACKAGE_ID }),
            }).then(r => r.json()).then(d => {
                if (d.error) throw new Error(d.error);
                return d.id;
            });
        },
        onApprove: function (data) {
            return fetch('/paypal/capture-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ order_id: data.orderID, package_id: PACKAGE_ID }),
            }).then(r => r.json()).then(d => {
                if (!d.success) throw new Error(d.error ?? @json(__('messages.pay.checkout_unknown_error')));
                window.location.href = SUCCESS_URL;
            });
        },
        onError: function () {
            window.location.href = FAIL_URL;
        },
    }).render('#paypal-button-container').then(function () {
        if (statusEl) statusEl.style.display = 'none';
    }).catch(function (err) {
        console.error(err);
        setPaymentError(@json(__('messages.pay.paypal_render_error')));
    });
})();
</script>
@else
<script>
    document.getElementById('payment-status').textContent = @json(__('messages.pay.paypal_not_configured'));
    document.getElementById('payment-status').style.color = '#dc2626';
</script>
@endif
@endsection
