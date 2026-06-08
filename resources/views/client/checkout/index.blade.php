@extends('layout.novelight')

@section('template_title', 'Checkout')

@section('page_css')
<style>
.checkout-wrap {
    max-width: 680px;
    margin: 40px auto;
    padding: 0 16px 60px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}
.checkout-wrap h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 24px;
    color: var(--text-primary, #111);
}
.checkout-card {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
}
.checkout-card h2 {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 16px;
    color: var(--text-primary, #111);
}
/* Order summary row */
.order-item {
    display: flex;
    align-items: center;
    gap: 16px;
}
.order-item img {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}
.order-item-icon {
    width: 72px;
    height: 72px;
    border-radius: 10px;
    background: linear-gradient(135deg,#7c3aed,#4f46e5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}
.order-item-info { flex: 1; }
.order-item-info strong { display: block; font-size: 1rem; }
.order-item-info span  { font-size: 0.875rem; color: var(--text-muted,#6b7280); }
.order-item-price {
    font-size: 1rem;
    font-weight: 600;
    white-space: nowrap;
}
/* Payment method list */
.pay-method-list { display: flex; flex-direction: column; gap: 12px; }
.pay-method {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 18px;
    border: 1.5px solid var(--border, #e5e7eb);
    border-radius: 10px;
    cursor: pointer;
    transition: border-color .15s, background .15s;
}
.pay-method:hover { border-color: #6366f1; background: #f5f3ff; }
.pay-method .pm-icon {
    width: 52px;
    height: 38px;
    border-radius: 6px;
    object-fit: contain;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}
.pay-method span { font-weight: 500; font-size: 0.95rem; }
/* PayPal button container */
#paypal-button-container { margin-top: 8px; }
.checkout-notice {
    font-size: 0.8rem;
    color: var(--text-muted,#6b7280);
    text-align: center;
    margin-top: 12px;
}
/* Success overlay */
#checkout-success {
    display: none;
    text-align: center;
    padding: 32px 16px;
}
#checkout-success .success-icon { font-size: 3rem; margin-bottom: 12px; }
#checkout-success h3 { font-size: 1.3rem; font-weight: 700; margin-bottom: 8px; }
#checkout-success p  { color: var(--text-muted,#6b7280); margin-bottom: 20px; }
</style>
@endsection

@section('content')
<div class="checkout-wrap">
    <h1>Checkout</h1>

    {{-- Order summary --}}
    <div class="checkout-card">
        <h2>Order summary</h2>
        <div class="order-item">
            @if($package->icon)
                <img src="{{ asset($package->icon) }}" alt="{{ $package->name }}">
            @else
                <div class="order-item-icon">🪙</div>
            @endif
            <div class="order-item-info">
                <strong>{{ $package->name }}</strong>
                <span>Buy {{ number_format($package->coins) }} coupons for ${{ number_format($package->price_usd, 0) }}</span>
            </div>
            <div class="order-item-price">${{ number_format($package->price_usd, 2) }}</div>
        </div>
    </div>

    {{-- Payment --}}
    <div class="checkout-card" id="payment-card">
        <h2>Payment methods</h2>

        {{-- PayPal smart buttons --}}
        <div id="paypal-button-container"></div>

        <p class="checkout-notice">
            <i class="fa fa-lock"></i> Thanh toán bảo mật qua PayPal. Sau khi xác nhận xu sẽ được cộng ngay vào tài khoản.
        </p>
    </div>

    {{-- Success state --}}
    <div class="checkout-card" id="checkout-success">
        <div class="success-icon">✅</div>
        <h3>Thanh toán thành công!</h3>
        <p id="success-msg"></p>
        <a href="{{ route('users.show') }}" class="btn btn-primary">Xem tài khoản</a>
        <a href="{{ route('pages.pricing') }}" class="btn btn-outline-secondary ms-2">Mua thêm</a>
    </div>
</div>
@endsection

@section('page_js')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD" crossorigin="anonymous"></script>
<script>
(function () {
    const PACKAGE_ID = {{ $package->id }};
    const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    paypal.Buttons({
        style: {
            layout : 'vertical',
            color  : 'blue',
            shape  : 'rect',
            label  : 'pay',
            height : 48,
        },

        // Bước 1: tạo order trên server
        createOrder: function () {
            return fetch('/paypal/create-order', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body   : JSON.stringify({ package_id: PACKAGE_ID }),
            })
            .then(r => r.json())
            .then(d => {
                if (d.error) throw new Error(d.error);
                return d.id;
            });
        },

        // Bước 2: capture sau khi user approve
        onApprove: function (data) {
            return fetch('/paypal/capture-order', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body   : JSON.stringify({ order_id: data.orderID, package_id: PACKAGE_ID }),
            })
            .then(r => r.json())
            .then(d => {
                if (!d.success) throw new Error(d.error ?? 'Lỗi không xác định');
                document.getElementById('payment-card').style.display  = 'none';
                document.getElementById('checkout-success').style.display = 'block';
                document.getElementById('success-msg').textContent = d.message;
            });
        },

        onError: function (err) {
            console.error(err);
            alert('Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.');
        },

        onCancel: function () {
            // user đóng popup PayPal, không làm gì
        },
    }).render('#paypal-button-container');
})();
</script>
@endsection
