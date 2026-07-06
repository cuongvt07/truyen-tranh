@extends('layout.novelight')

@section('template_title', __('messages.pay.payment_success_title'))

@section('content')
<div class="container">
    <div class="purchase-result block">
        <div class="result-icon success">✅</div>
        <h1>{{ __('messages.pay.payment_success_title') }}</h1>
        <p class="result-msg">
            {{ $result['message'] ?? __('messages.pay.purchase_success_desc') }}
        </p>
        <div class="result-actions">
            @auth
                <a href="{{ route('users.transactions', auth()->id()) }}" class="btn btn-primary">
                    <i class="fa fa-receipt"></i> {{ __('messages.pay.view_transactions') }}
                </a>
            @endauth
            <a href="{{ route('pages.pricing') }}" class="btn btn-outline-secondary">{{ __('messages.pay.buy_more') }}</a>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">{{ __('messages.pay.back_home') }}</a>
        </div>
    </div>
</div>

@if($result && !empty($result['transaction_id']))
{{-- Google Ads / GA4 purchase conversion — chỉ bắn 1 lần trên trang cảm ơn khi có giao dịch thật. --}}
<script>
    window.addEventListener('load', function () {
        if (typeof gtag === 'function') {
            gtag('event', 'conversion_event_purchase', {
                value: @json($result['value'] ?? null),
                currency: @json($result['currency'] ?? 'USD'),
                transaction_id: @json($result['transaction_id']),
            });
        }
    });
</script>
@endif

<style>
.purchase-result { max-width:560px; margin:40px auto; padding:40px 28px; text-align:center; }
.purchase-result .result-icon { font-size:3.4rem; line-height:1; margin-bottom:14px; }
.purchase-result h1 { font-size:1.5rem; font-weight:700; margin-bottom:10px; }
.purchase-result .result-msg { color:var(--meta-color,#6b7280); margin-bottom:24px; line-height:1.6; }
.purchase-result .result-actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
</style>
@endsection
