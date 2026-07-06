@extends('layout.novelight')

@section('template_title', __('messages.pay.purchase_fail_title'))

@section('content')
<div class="container">
    <div class="purchase-result block">
        <div class="result-icon fail">❌</div>
        <h1>{{ __('messages.pay.purchase_fail_title') }}</h1>
        <p class="result-msg">{{ __('messages.pay.purchase_fail_desc') }}</p>
        <div class="result-actions">
            <a href="{{ route('pages.pricing') }}" class="btn btn-primary">
                <i class="fa fa-rotate-right"></i> {{ __('messages.pay.try_again') }}
            </a>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">{{ __('messages.pay.back_home') }}</a>
        </div>
    </div>
</div>

<style>
.purchase-result { max-width:560px; margin:40px auto; padding:40px 28px; text-align:center; }
.purchase-result .result-icon { font-size:3.4rem; line-height:1; margin-bottom:14px; }
.purchase-result h1 { font-size:1.5rem; font-weight:700; margin-bottom:10px; }
.purchase-result .result-msg { color:var(--meta-color,#6b7280); margin-bottom:24px; line-height:1.6; }
.purchase-result .result-actions { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
</style>
@endsection
