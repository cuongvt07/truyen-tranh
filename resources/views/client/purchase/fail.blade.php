@extends('layout.novelight')

@section('template_title', __('messages.pay.purchase_fail_title'))

@section('content')
<div class="alpha-workspace">
    <div class="container">
        <section class="alpha-panel alpha-panel--pad" style="max-width:620px;margin:34px auto;text-align:center">
            <div class="alpha-order-icon" style="margin:0 auto 16px;color:#dc2626"><i class="fa fa-times"></i></div>
            <h1 class="alpha-section-title">{{ __('messages.pay.purchase_fail_title') }}</h1>
            <p class="meta-color" style="line-height:1.7;margin-bottom:22px">{{ __('messages.pay.purchase_fail_desc') }}</p>
            <div class="alpha-form-actions" style="justify-content:center">
                <a href="{{ route_path('pages.pricing') }}" class="alpha-btn alpha-btn--primary">
                    <i class="fa fa-rotate-right"></i> {{ __('messages.pay.try_again') }}
                </a>
                <a href="{{ url('/') }}" class="alpha-btn">{{ __('messages.pay.back_home') }}</a>
            </div>
        </section>
    </div>
</div>
@endsection
