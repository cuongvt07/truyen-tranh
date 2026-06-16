@extends('layout.novelight')

@section('template_title', __('messages.pay.store'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/payments/css/buy_coupons.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">{{ __('messages.pay.store') }}</h1>

    {{-- Nổi bật --}}
    <div class="huge-recomendations">
        {{-- Gói xu nổi bật --}}
        <div class="recommended-product block">
            <a href="{{ !empty($featured['id']) ? route('checkout.show', $featured['id']) : route('client.paypoints') }}" class="image image-cover lazy-load-bg">
                <img class="lazy-image" loading="eager" src="{{ asset($featured['icon']) }}" alt="{{ $featured['name'] }}">
            </a>
            <div class="recommended-product__info">
                <div class="recommended-product__info-title">
                    <h2 class="price_item__title block-title">{{ $featured['name'] }}</h2>
                    <div class="recommended-coupons"><i class="fa fa-coins" style="color:#f0c040"></i> {{ number_format($featured['coins']) }} {{ coin_name() }}</div>
                </div>
                @auth
                    @if(!empty($featured['id']))
                        <a href="{{ route('checkout.show', $featured['id']) }}" class="btn btn-primary">{{ $featured['price'] }}</a>
                    @else
                        <a href="{{ route('client.paypoints') }}" class="btn btn-primary">{{ $featured['price'] }}</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">{{ $featured['price'] }}</a>
                @endauth
            </div>
        </div>

        {{-- Premium (chỉ hiện khi có gói subscription thật) --}}
        @if(!empty($premium))
        <div class="recommended-product block">
            <a href="{{ !empty($premium['id']) ? route('checkout.show', $premium['id']) : route('client.paypoints') }}" class="image image-cover lazy-load-bg">
                <img class="lazy-image" loading="eager" src="{{ asset($premium['icon']) }}" alt="{{ $premium['name'] }}">
            </a>
            <div class="recommended-product__info">
                <div class="recommended-product__info-title">
                    <h2 class="price_item__title block-title">{{ $premium['name'] }}</h2>
                    <div class="recommended-coupons"><i class="fa fa-crown" style="color:#f0c040"></i> {{ $premium['desc'] }}</div>
                </div>
                @auth
                    @if(!empty($premium['id']))
                        <a href="{{ route('checkout.show', $premium['id']) }}" class="btn btn-primary">{{ $premium['price'] }}</a>
                    @else
                        <a href="{{ route('client.paypoints') }}" class="btn btn-primary">{{ $premium['price'] }}</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">{{ $premium['price'] }}</a>
                @endauth
            </div>
        </div>
        @endif
    </div>

    {{-- Tất cả sản phẩm --}}
    <div class="section">
        <h2>{{ __('messages.pay.all_products') }}</h2>
        <div class="price-list">
            {{-- Premium card (chỉ hiện khi có gói subscription thật) --}}
            @if(!empty($premium))
            <div class="price-item block">
                <div class="price_item__icon image image-cover lazy-load-bg">
                    <img class="lazy-image" loading="eager" src="{{ asset($premium['icon']) }}" alt="">
                </div>
                <div class="price-item__info">
                    <h2 class="price_item__title block-title">{{ $premium['name'] }}</h2>
                    <div class="price-item__cost-info">
                        <div class="price-item__cost"><span>{{ $premium['desc'] }}</span><br>{{ $premium['price'] }}</div>
                        @auth
                            @if(!empty($premium['id']))
                                <a href="{{ route('checkout.show', $premium['id']) }}" class="btn btn-primary">{{ __('messages.pay.buy') }}</a>
                            @else
                                <a href="{{ route('client.paypoints') }}" class="btn btn-primary">{{ __('messages.pay.buy') }}</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">{{ __('messages.pay.buy') }}</a>
                        @endauth
                    </div>
                </div>
            </div>
            @endif

            {{-- Coin packs --}}
            @foreach($coinPacks as $pack)
                <div class="price-item block">
                    <div class="price_item__icon image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="eager" src="{{ asset($pack['icon']) }}" alt="{{ $pack['name'] }}">
                    </div>
                    <div class="price-item__info">
                        <h2 class="price_item__title block-title">{{ $pack['name'] }}</h2>
                        <div class="price-item__cost-info">
                            <div class="price-item__cost"><span>{{ number_format($pack['coins']) }} {{ coin_name() }}</span><br>{{ $pack['price'] }}</div>
                            @auth
                                @if(!empty($pack['id']))
                                    <a href="{{ route('checkout.show', $pack['id']) }}" class="btn btn-primary">{{ __('messages.pay.buy') }}</a>
                                @else
                                    <a href="{{ route('client.paypoints') }}" class="btn btn-primary">{{ __('messages.pay.buy') }}</a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary">{{ __('messages.pay.buy') }}</a>
                            @endauth
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
