@extends('layout.novelight')

@section('template_title', __('messages.pay.payment_success_title'))

@section('content')
<div class="alpha-workspace">
    <div class="container">
        <section class="alpha-panel alpha-panel--pad" style="max-width:620px;margin:34px auto;text-align:center">
            <div class="alpha-order-icon" style="margin:0 auto 16px"><i class="fa fa-check"></i></div>
            <h1 class="alpha-section-title">{{ __('messages.pay.payment_success_title') }}</h1>
            <p class="meta-color" style="line-height:1.7;margin-bottom:22px">
                {{ $result['message'] ?? __('messages.pay.purchase_success_desc') }}
            </p>
            <div class="alpha-form-actions" style="justify-content:center">
                @auth
                    <a href="{{ route_path('users.transactions', auth()->id()) }}" class="alpha-btn alpha-btn--primary">
                        <i class="fa fa-receipt"></i> {{ __('messages.pay.view_transactions') }}
                    </a>
                @endauth
                <a href="{{ route_path('pages.pricing') }}" class="alpha-btn">{{ __('messages.pay.buy_more') }}</a>
                <a href="{{ url('/') }}" class="alpha-btn">{{ __('messages.pay.back_home') }}</a>
            </div>
        </section>
    </div>
</div>

@if($result && !empty($result['transaction_id']))
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
@endsection
