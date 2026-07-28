@extends('layout.novelight')

@php
    $currentUser = $currentUser ?? auth()->user();
    $avatar = $user->avatar ?: asset('static/account/images/no-ava.jpg');
    $panelBg = $user->background ?: $avatar;
    $isMine = $isMine ?? isMyAccount($currentUser, $user);
    $bookmarkStatsQuery = method_exists($user, 'bookmarks')
        ? $user->bookmarks()->whereHas('article')
        : null;
    if ($bookmarkStatsQuery && !$isMine) {
        $bookmarkStatsQuery->where('is_public', true);
    }
    $libraryCount = $bookmarkStatsQuery ? (clone $bookmarkStatsQuery)->count() : 0;
    $readingStoriesCount = $bookmarkStatsQuery ? (clone $bookmarkStatsQuery)->count() : 0;
    $commentCount = method_exists($user, 'comments') ? $user->comments()->count() : 0;
    $navItems = [
        ['route' => route_path('users.show.profile', $user->id), 'active' => ['users.show', 'users.show.profile', 'users.show_posted_articles'], 'icon' => 'fa-list', 'label' => __('messages.account.nav_profile')],
        ['route' => route_path('users.show_bookmarks', $user->id), 'active' => ['users.show_bookmarks'], 'icon' => 'fa-bookmark', 'label' => __('messages.account.nav_bookmarks')],
        ['route' => route_path('users.reading_history', $user->id), 'active' => ['users.reading_history'], 'icon' => 'fa-history', 'label' => __('messages.account.nav_reading_history')],
        ['route' => route_path('users.collections', $user->id), 'active' => ['users.collections'], 'icon' => 'fa-layer-group', 'label' => __('messages.account.nav_collections')],
        ['route' => route_path('users.favourites', $user->id), 'active' => ['users.favourites'], 'icon' => 'fa-heart', 'label' => __('messages.account.nav_following')],
        ['route' => route_path('users.achievements', $user->id), 'active' => ['users.achievements'], 'icon' => 'fa-award', 'label' => __('messages.account.nav_achievements')],
        ['route' => route_path('users.suggestions', $user->id), 'active' => ['users.suggestions'], 'icon' => 'fa-lightbulb', 'label' => __('messages.account.nav_suggestions')],
        ['route' => route_path('users.show_comments', $user->id), 'active' => ['users.show_comments'], 'icon' => 'fa-comment', 'label' => __('messages.account.nav_comments')],
        ['route' => route_path('users.banlist', $user->id), 'active' => ['users.banlist'], 'icon' => 'fa-ban', 'label' => __('messages.account.nav_banlist')],
    ];
    if ($isMine) {
        $navItems[] = ['route' => route_path('users.notifications', $user->id), 'active' => ['users.notifications'], 'icon' => 'fa-bell', 'label' => __('messages.account.nav_notifications')];
        $navItems[] = ['route' => route_path('users.transactions', $user->id), 'active' => ['users.transactions'], 'icon' => 'fa-coins', 'label' => __('messages.account.nav_transactions')];
        $navItems[] = ['route' => route_path('users.change_info'), 'active' => ['users.change_info', 'users.change_password'], 'icon' => 'fa-cog', 'label' => __('messages.account.nav_settings')];
    }
@endphp

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/account/css/login.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="alpha-account-page">
    <section class="alpha-account-hero" style="--alpha-account-bg: url('{{ $panelBg }}')">
        <div class="container alpha-account-hero__inner">
            <div class="alpha-account-avatar {{ user_is_vip($user->id) ? 'vip-ring' : '' }}">
                <img src="{{ $avatar }}" alt="{{ $user->username }}" loading="eager">
                @include('partials.vip-crown', ['userId' => $user->id])
            </div>

            <div class="alpha-account-identity">
                <small>{{ $isMine ? 'My Library' : 'Reader Profile' }}</small>
                <h1>{!! method_exists($user, 'renderUserName') ? $user->renderUserName() : e($user->username) !!}</h1>
                @if(!empty($user->description))
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($user->description), 150) }}</p>
                @endif
                <div class="alpha-account-stats">
                    <span><b>{{ number_format($libraryCount) }}</b> Library</span>
                    <span><b>{{ number_format($readingStoriesCount) }}</b> Reading</span>
                    <span><b>{{ number_format($commentCount) }}</b> Reviews</span>
                    @if(!empty($activeVipDays) && $activeVipDays > 0)
                        <span class="alpha-account-vip"><i class="fa fa-crown"></i> VIP {{ $activeVipDays }}d</span>
                    @endif
                </div>
            </div>

            @if($isMine)
                <div class="alpha-account-actions">
                    <a href="{{ route_path('pages.pricing') }}" class="alpha-account-action alpha-account-action--primary"><i class="fa fa-gift"></i> Gifts</a>
                    <a href="{{ route_path('users.change_info') }}" class="alpha-account-action"><i class="fa fa-cog"></i> Edit</a>
                </div>
            @endif
        </div>
    </section>

    <div class="container alpha-account-shell">
        <aside class="alpha-account-nav" aria-label="Account navigation">
            @foreach($navItems as $item)
                <a href="{{ $item['route'] }}" class="{{ request()->routeIs(...$item['active']) ? 'active' : '' }}">
                    <i class="fa {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </aside>

        <main class="alpha-account-main">
            @yield('user_content')
        </main>
    </div>
</div>
@endsection
