@extends('layout.novelight')

@section('template_title', __('messages.pay.store'))

@section('content')
@php
    $featured = $featured ?? null;
    $coinPacks = collect($coinPacks ?? []);
    $storeCards = $coinPacks->values();
    $balance = auth()->check() ? (auth()->user()->points ?? 0) : 0;
    $dailyService = app(\App\Services\DailyCheckinService::class);
    $dailyEnabled = $dailyService->enabled();
    $dailyClaimed = auth()->check() ? $dailyService->hasClaimed(auth()->user()) : false;
    $dailyRewards = collect(range(0, 6))->map(function ($offset) use ($dailyService) {
        $date = now()->copy()->addDays($offset);
        return [
            'label' => $offset === 0 ? 'Today' : $date->format('D'),
            'amount' => $dailyService->rewardForDate($date),
            'today' => $offset === 0,
        ];
    });
@endphp

<div class="alpha-gifts-page">
    <section class="alpha-gifts-hero">
        <div class="container alpha-gifts-hero__inner">
            <div class="alpha-gifts-hero__icon">
                <img src="/static/core/images/alphanovel/present.png" alt="Gift">
            </div>
            <div class="alpha-gifts-hero__copy">
                <small>Rewards and bonuses</small>
                <h1>Unlock more stories on {{ config('app.name', 'Romane auf Deutsch') }}</h1>
                <p>Use coins for locked chapters, reader actions, and premium access while keeping every novel in your online library.</p>
                <div class="alpha-gifts-hero__actions">
                    <a href="#coin-packs" class="alpha-button alpha-gifts-hero__button alpha-gifts-hero__button--light"><i class="fa fa-coins"></i> {{ __('messages.access.view_coin_packs') }}</a>
                    <a href="{{ route_path('catalog.index', []) }}" class="alpha-button alpha-gifts-hero__button"><i class="fa fa-book-open"></i> {{ __('messages.access.browse_novels') }}</a>
                </div>
            </div>

            <div class="alpha-gifts-summary">
                <strong>{{ number_format($balance) }}</strong>
                <span>{{ coin_name() }} balance</span>
                <small>Top up once, unlock chapters anytime.</small>
            </div>
        </div>
    </section>

    <section class="container alpha-gifts-showcase">
        @foreach(range(1, 5) as $giftImage)
            <article>
                <img src="/static/core/images/alphanovel/gifts-{{ $giftImage }}.png" alt="Gift reward {{ $giftImage }}" loading="lazy">
            </article>
        @endforeach
    </section>

    <div class="container alpha-gifts-content">
        @if(session('reading_limit_notice'))
            <div class="alpha-store-alert">{{ session('reading_limit_notice') }}</div>
        @endif

        @if($dailyEnabled)
            <section class="alpha-daily-gifts-card">
                <div class="alpha-daily-gifts-card__visual">
                    <img src="/static/core/images/alphanovel/present.png" alt="Daily bonus" loading="lazy">
                    <span><i class="fa fa-gift"></i> Daily Bonus</span>
                </div>
                <div class="alpha-daily-gifts-card__copy">
                    <small>Daily check-in</small>
                    <h2>Collect free {{ coin_name() }} every day</h2>
                    <p>Log in, open the reward calendar, and claim today's bonus before you continue reading.</p>
                    <div class="alpha-daily-gifts-card__calendar" aria-label="Daily bonus preview">
                        @foreach($dailyRewards as $reward)
                            <span class="{{ $reward['today'] ? 'is-today' : '' }}">
                                <b>{{ $reward['label'] }}</b>
                                <small>+{{ number_format($reward['amount']) }}</small>
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="alpha-daily-gifts-card__action">
                    @auth
                        @if($dailyClaimed)
                            <span class="alpha-gift-button alpha-gift-button--disabled"><i class="fa fa-check"></i> Claimed today</span>
                        @else
                            <button type="button" class="alpha-gift-button alpha-gift-button--primary" data-daily-open>
                                <i class="fa fa-coins"></i> Claim now
                            </button>
                        @endif
                    @else
                        <a href="{{ route_path('login', []) }}" class="alpha-gift-button alpha-gift-button--primary">Login to claim</a>
                    @endauth
                </div>
            </section>
        @endif

        <div class="alpha-store-wallet">
            @auth
                <span class="alpha-balance-pill"><i class="fa fa-coins"></i> {{ number_format($balance) }} {{ coin_name() }}</span>
            @else
                <a href="{{ route_path('login', []) }}" class="alpha-gift-button alpha-gift-button--primary">{{ __('messages.auth.login') }}</a>
            @endauth
            <a href="{{ route_path('catalog.index', []) }}" class="alpha-gift-button">{{ __('messages.access.browse_novels') }}</a>
        </div>

        <section class="alpha-store-featured">
            @if($featured)
                <article class="alpha-store-spotlight">
                    <div class="alpha-store-spotlight__media">
                        <img src="{{ asset($featured['icon']) }}" alt="{{ $featured['name'] }}" loading="lazy">
                    </div>
                    <div>
                        <small>Recommended</small>
                        <h2>{{ $featured['name'] }}</h2>
                        <p>{{ number_format($featured['coins']) }} {{ coin_name() }} for unlocking chapters and reader actions.</p>
                    </div>
                    @auth
                        <a href="{{ !empty($featured['id']) ? route_path('checkout.show', $featured['id']) : route_path('client.paypoints') }}" class="alpha-gift-button alpha-gift-button--primary">{{ $featured['price'] }}</a>
                    @else
                        <a href="{{ route_path('login', []) }}" class="alpha-gift-button alpha-gift-button--primary">{{ $featured['price'] }}</a>
                    @endauth
                </article>
            @endif

            @if(!empty($premium))
                <article class="alpha-store-spotlight alpha-store-spotlight--premium">
                    <div class="alpha-store-spotlight__media">
                        <img src="{{ asset($premium['icon']) }}" alt="{{ $premium['name'] }}" loading="lazy">
                    </div>
                    <div>
                        <small>Premium access</small>
                        <h2>{{ $premium['name'] }}</h2>
                        <p>{{ $premium['desc'] }}</p>
                    </div>
                    @auth
                        <a href="{{ !empty($premium['id']) ? route_path('checkout.show', $premium['id']) : route_path('client.paypoints') }}" class="alpha-gift-button alpha-gift-button--primary">{{ $premium['price'] }}</a>
                    @else
                        <a href="{{ route_path('login', []) }}" class="alpha-gift-button alpha-gift-button--primary">{{ $premium['price'] }}</a>
                    @endauth
                </article>
            @endif
        </section>

        <section class="alpha-store-section" id="coin-packs">
            <div class="alpha-section-heading">
                <h2>{{ __('messages.pay.all_products') }}</h2>
                <a href="{{ route_path('client.paypoints') }}">Manual top up</a>
            </div>

            <div class="alpha-store-grid">
                @if(!empty($premium))
                    <article class="alpha-store-card alpha-store-card--premium">
                        <img src="{{ asset($premium['icon']) }}" alt="{{ $premium['name'] }}" loading="lazy">
                        <small>Premium</small>
                        <h3>{{ $premium['name'] }}</h3>
                        <p>{{ $premium['desc'] }}</p>
                        <div class="alpha-store-card__bottom">
                            <strong>{{ $premium['price'] }}</strong>
                            @auth
                                <a href="{{ !empty($premium['id']) ? route_path('checkout.show', $premium['id']) : route_path('client.paypoints') }}">{{ __('messages.pay.buy') }}</a>
                            @else
                                <a href="{{ route_path('login', []) }}">{{ __('messages.pay.buy') }}</a>
                            @endauth
                        </div>
                    </article>
                @endif

                @forelse($storeCards as $pack)
                    <article class="alpha-store-card">
                        <img src="{{ asset($pack['icon']) }}" alt="{{ $pack['name'] }}" loading="lazy">
                        <small>Coin pack</small>
                        <h3>{{ $pack['name'] }}</h3>
                        <p>{{ number_format($pack['coins']) }} {{ coin_name() }} added to your reader wallet.</p>
                        <div class="alpha-store-card__bottom">
                            <strong>{{ $pack['price'] }}</strong>
                            @auth
                                <a href="{{ !empty($pack['id']) ? route_path('checkout.show', $pack['id']) : route_path('client.paypoints') }}">{{ __('messages.pay.buy') }}</a>
                            @else
                                <a href="{{ route_path('login', []) }}">{{ __('messages.pay.buy') }}</a>
                            @endauth
                        </div>
                    </article>
                @empty
                    <div class="alpha-empty">No packages are available.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
