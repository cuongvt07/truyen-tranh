<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    @php
        $seoSep   = seo_setting('title_separator', ' · ');
        $seoSite  = seo_setting('site_name', config('app.name', __('messages.layout.default_site_name')));
        $seoDesc  = trim($__env->yieldContent('meta_description')) ?: seo_setting('default_description', __('messages.layout.default_meta_description'));
        $seoOg    = trim($__env->yieldContent('og_image')) ?: asset(ltrim(seo_setting('default_og_image', '/static/core/images/no_cover.webp'), '/'));
        $seoTitle = trim($__env->yieldContent('template_title'));
        $seoFullTitle = ($seoTitle ? $seoTitle . $seoSep : '') . $seoSite;
        $seoCanonical = trim($__env->yieldContent('canonical_url')) ?: url()->current();
    @endphp
    <title>{{ $seoFullTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($seoDesc), 160) }}">
    <meta name="keywords" content="{{ seo_setting('default_keywords', __('messages.layout.default_keywords')) }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @php $favicon = setting('favicon_file') ? asset('storage/' . setting('favicon_file')) : asset('static/favicon.ico'); @endphp
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
    <meta name="theme-color" content="#1e2a3a">

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $seoSite }}">
    <meta property="og:title" content="{{ $seoTitle ?: $seoSite }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($seoDesc), 160) }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoOg }}">
    <meta property="og:locale" content="{{ seo_setting('og_locale', 'vi_VN') }}">
    @if(seo_setting('facebook_app_id'))<meta property="fb:app_id" content="{{ seo_setting('facebook_app_id') }}">@endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle ?: $seoSite }}">
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($seoDesc), 160) }}">
    <meta name="twitter:image" content="{{ $seoOg }}">
    @if(seo_setting('twitter_username'))<meta name="twitter:site" content="{{ seo_setting('twitter_username') }}">@endif

    {{-- Verification --}}
    @if(seo_setting('google_site_verify'))<meta name="google-site-verification" content="{{ seo_setting('google_site_verify') }}">@endif
    @if(seo_setting('bing_site_verify'))<meta name="msvalidate.01" content="{{ seo_setting('bing_site_verify') }}">@endif

    {{-- Google Tag Manager / Analytics --}}
    @if(seo_setting('google_tag_manager'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ seo_setting('google_tag_manager') }}');</script>
    @elseif(seo_setting('google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ seo_setting('google_analytics_id') }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ seo_setting('google_analytics_id') }}');</script>
    @endif

    {{-- Structured Data / Schema.org --}}
    @stack('schema')

    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Play:wght@400;700&family=Roboto:wght@100;400;500&display=swap" rel="stylesheet">

    @php
        $assetVer = function ($p) { $f = public_path($p); return file_exists($f) ? filemtime($f) : '1.8.0'; };
    @endphp
    <link rel="stylesheet" href="{{ asset('static/core/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('static/core/css/swiper.bundle.css') }}">
    <link rel="stylesheet" href="{{ asset('static/core/css/fontawesomeee8b.css') }}?ver={{ $assetVer('static/core/css/fontawesomeee8b.css') }}">
    <link rel="stylesheet" href="{{ asset('static/core/css/styleee8b.css') }}?ver={{ $assetVer('static/core/css/styleee8b.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/flag-icon-css/css/flag-icons.min.css') }}">
    @yield('page_css')
    @stack('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Ensure lazy images are always visible regardless of JS lazy-load state */
        img.lazy-image { opacity: 1 !important; visibility: visible !important; }

        /* Ẩn nút next/prev và dots của swiper trên trang chủ (không dùng) */
        .popular .swiper-button-next, .popular .swiper-button-prev,
        .new-realeses .swiper-pagination, .translation-requests .swiper-pagination,
        .swiper-pagination-bullets.swiper-pagination-horizontal { display: none !important; }

        /* Chặn tràn ngang toàn site */
        html, body { max-width: 100%; overflow-x: hidden; }

        /* Responsive cho các trang tự build (≤620px) */
        @media (max-width: 620px) {
            /* Header: thu gọn để không tràn ngang */
            .header__inner { gap: 6px; }
            .header-user { gap: 6px; }
            .header-user .btn { padding: 6px 9px; font-size: 12px; }
            .header-user .header-coins { font-size: 13px; }
            .header-avatar { width: 32px; height: 32px; }
            /* Auth (login/register) — neo theo viewport thật để không tràn */
            .container-login { width: calc(100vw - 20px) !important; max-width: calc(100vw - 20px) !important; padding-left: 6px !important; padding-right: 6px !important; }
            .container-login .login-form, .container-login .block { max-width: 100% !important; }
            .container-login .text-input, .container-login input { max-width: 100% !important; box-sizing: border-box; }
            .container-login .control-btn { flex-wrap: wrap; gap: 10px; }
            .container-login .control-btn .btn { min-width: 0 !important; flex: 1; }
            .container-login .control-btn .forgot-password-link { flex: 1 1 100%; }

            /* Pricing */
            .huge-recomendations { grid-template-columns: 1fr !important; }
            .recommended-product__info { flex-wrap: wrap; gap: 8px; }
            .recommended-product__info .btn { min-width: 0 !important; }
            .price-list { grid-template-columns: 1fr !important; }
            .price-item__cost-info .btn { white-space: nowrap; }

            /* Profile / settings / transactions: sidebar lên trên, full width */
            .flex-content { flex-direction: column; }
            .flex-content .second-information,
            .flex-content .main { width: 100% !important; max-width: 100% !important; }
            .user-nav { flex-direction: row; flex-wrap: wrap; }
            .user-nav .btn { flex: 1 1 auto; }
            .header-user-page { flex-direction: column; align-items: center; text-align: center; }
            .trans-table.full { font-size: 12px; }
            .trans-table.full th, .trans-table.full td { padding: 6px 8px; }
        }

        /* Logged-in header controls */
        .header-user { display: flex; align-items: center; gap: 14px; }
        .header-user .header-btn { cursor: pointer; display: flex; align-items: center; gap: 5px; color: #fff; font-size: 17px; }
        .header-user .header-coins { font-size: 15px; gap: 4px; }
        .header-user .header-coins .fa-coins { color: #f0c040; }
        .header-user .header-lang { font-size: 16px; gap: 3px; }
        .header-user .header-bell:hover, .header-user .header-add:hover { opacity: .8; }
        .header-avatar { width: 38px; height: 38px; border-radius: 4px; overflow: hidden; border: 1px solid rgba(255,255,255,.3); }
        .header-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .header-sublist .fa { width: 18px; text-align: center; margin-right: 4px; }
    </style>
</head>
<body>
@php
    $navGenres = \App\Models\Genre::orderBy('name')->get();
@endphp
<header class="header">
    <div class="container">
        <div class="header__inner">
            <div id="header-mobile-btn" class="header-btn open-close" p-target="fullscreen-mobile-menu"
                 p-target-class="active" p-event="burgerMenuOpenClose"><i class="fa fa-bars"></i></div>
            @php $siteName = setting('site_name') ?: config('app.name'); @endphp
            <a href="{{ route('home.index') }}" class="logo">
                @if(setting('logo_file'))
                    <img src="{{ asset('storage/' . setting('logo_file')) }}" alt="{{ $siteName }}">
                @else
                    <img src="{{ asset('static/core/images/logo.png') }}" alt="{{ $siteName }}">
                @endif
            </a>
            <nav class="header-nav">
                <ul>
                    @forelse(menu_items('header') as $mi)
                        @include('partials.menu-header-item', ['mi' => $mi])
                    @empty
                        {{-- Fallback: nav mặc định khi chưa cấu hình menu --}}
                        <li><a href="{{ route('home.index') }}" class="header-btn no-link"><i class="fa fa-home"></i> {{ __('messages.nav.home') }}</a></li>
                        <li class="header-nav__list"><div class="header-btn header-browse tippy-browse"><i class="fa fa-layer-group"></i> {{ __('messages.nav.browse') }} <i class="fa fa-caret-down"></i></div></li>
                        <li><a href="#" id="open-live-search" class="header-btn no-link open-close" p-target="fullscreen-search"><i class="fa fa-search"></i> {{ __('messages.nav.search') }}</a></li>
                        <li><a href="{{ route('pages.forum') }}" class="header-btn no-link"><i class="fa fa-comments"></i> {{ __('messages.nav.forum') }}</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="header-btn no-link"><i class="fa fa-question-circle"></i> {{ __('messages.nav.faq') }}</a></li>
                        <li><a href="{{ route('home.show_new_update_articles') }}" class="header-btn no-link"><i class="fa fa-bolt"></i> {{ __('messages.nav.new') }}</a></li>
                        <li><a href="{{ route('home.show_completed_articles') }}" class="header-btn no-link"><i class="fa fa-check-circle"></i> {{ __('messages.nav.completed') }}</a></li>
                    @endforelse
                </ul>
            </nav>
            <div class="header-user">
                @auth
                    @php $authUser = Auth::user(); $userAvatar = $authUser->avatar ?: asset('static/account/images/no-ava.jpg'); @endphp

                    {{-- Nút + (thêm) — mọi user đã đăng nhập --}}
                    <div id="add-item-btn" class="header-btn header-add"><i class="fa fa-plus"></i></div>

                    {{-- Chuông thông báo --}}
                    <a href="{{ route('users.notifications', Auth::id()) }}" class="header-btn header-bell" title="{{ __('messages.account.nav_notifications') }}">
                        <i class="fa fa-bell"></i>
                        @if(($unreadNotifCount ?? 0) > 0)<span class="alert"></span>@endif
                    </a>

                    {{-- Số xu --}}
                    <a href="{{ route('users.transactions', Auth::id()) }}" class="header-btn header-coins">
                        {{ number_format($authUser->points ?? 0) }}<i class="fa fa-coins"></i>
                    </a>

                    {{-- Avatar (mở menu) --}}
                    <div class="header-btn header-profile tippy-profile">
                        <div class="header-avatar"><img src="{{ $userAvatar }}" alt="{{ $authUser->username }}"></div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn login-btn">{{ __('messages.auth.login') }}</a>
                    <a href="{{ route('register') }}" class="btn register-btn">{{ __('messages.auth.register') }}</a>
                @endauth

                {{-- Language switcher --}}
                @if(config('locales.user_multilingual', true) && config('locales.switchable', true))
                    @php $curLocale = app()->getLocale(); $locales = config('locales.supported', []); @endphp
                    <div class="header-btn header-lang tippy-lang" title="{{ __('messages.layout.language') }}">
                        @if(!empty($locales[$curLocale]['flag_code']))
                            <span class="flag-icon flag-icon-{{ $locales[$curLocale]['flag_code'] }}"></span>
                        @else
                            🌐
                        @endif
                        <i class="fa fa-caret-down" style="font-size:11px"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>

<div class="page">
    <div class="content">
        @yield('content')

        <footer>
            <div class="footer-links">
                @forelse(menu_items('footer') as $mi)
                    <a href="{{ $mi->href }}"@if($mi->target === '_blank') target="_blank"@endif>{{ $mi->display_label }}</a>
                @empty
                    <a href="{{ route('pages.feedback') }}">{{ __('messages.footer.feedback') }}</a>
                    <a href="{{ route('pages.terms') }}">{{ __('messages.footer.terms') }}</a>
                    <a href="{{ route('pages.dmca') }}">{{ __('messages.footer.dmca') }}</a>
                    <a href="{{ route('pages.rules') }}">{{ __('messages.footer.rules') }}</a>
                    <a href="{{ route('pages.faq') }}">{{ __('messages.footer.faq') }}</a>
                @endforelse
            </div>
            <hr>
            <span>© {{ date('Y') }} {{ config('app.name') }}</span>
        </footer>
    </div>

    {{-- Fullscreen search overlay --}}
    <div id="fullscreen-search" class="fullscreen hide">
        <div class="fullscreen-container">
            <form action="{{ route('catalog.index') }}" method="GET">
                <div class="text-input">
                    <button id="close-fullscreen-search" type="button" class="open-close left-icon"
                            p-target="fullscreen-search"><i class="fa fa-close"></i></button>
                    <input id="ajax-base-search" name="search" type="text" placeholder="{{ __('messages.ui.search_placeholder') }}" autocomplete="off">
                    <button id="ajax-submit-search" type="submit" class="right-icon"><i class="fa fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="searches-container"></div>
    </div>

    {{-- Mobile menu --}}
    <div id="fullscreen-mobile-menu">
        <nav class="mobile-menu-list">
            <ul>
                @forelse(menu_items('mobile') as $mi)
                    @include('partials.menu-mobile-item', ['mi' => $mi])
                @empty
                    <li><a href="{{ route('home.index') }}"><i class="fa fa-home"></i> {{ __('messages.nav.home') }}</a></li>
                    <li><div class="tippy-browse"><span><i class="fa fa-layer-group"></i> {{ __('messages.nav.browse') }}</span> <i class="fa fa-caret-down"></i></div></li>
                    <li><a href="{{ route('pages.forum') }}"><i class="fa fa-comments"></i> {{ __('messages.nav.forum') }}</a></li>
                    <li><a href="{{ route('pages.faq') }}"><i class="fa fa-question-circle"></i> {{ __('messages.nav.faq') }}</a></li>
                    <li><a href="{{ route('home.show_new_update_articles') }}"><i class="fa fa-bolt"></i> {{ __('messages.nav.new') }}</a></li>
                    <li><a href="{{ route('home.show_completed_articles') }}"><i class="fa fa-check-circle"></i> {{ __('messages.nav.completed') }}</a></li>
                @endforelse
            </ul>

            {{-- Cụm chức năng tài khoản (đưa từ header xuống cho mobile) — hardcode, không qua menu admin --}}
            @auth
                @php $mAuth = Auth::user(); $mAva = $mAuth->avatar ?: asset('static/account/images/no-ava.jpg'); @endphp
                {{-- Cụm tài khoản dạng DROPDOWN: tap header để mở/đóng danh sách --}}
                <div class="mobile-menu-account" id="mma-toggle" role="button" tabindex="0" aria-expanded="false">
                    <div class="mma-ava"><img src="{{ $mAva }}" alt="{{ $mAuth->username }}"></div>
                    <div class="mma-info">
                        <span class="mma-name">{{ $mAuth->username }}</span>
                        <a href="{{ route('users.transactions', Auth::id()) }}" class="mma-coins"><i class="fa fa-coins"></i> {{ number_format($mAuth->points ?? 0) }}</a>
                    </div>
                    <i class="fa fa-caret-down mma-caret"></i>
                </div>
                <div class="mma-collapse">
                    <ul>
                        <li><a href="{{ route('users.show') }}"><i class="fa fa-user"></i> {{ __('messages.ui.menu_profile') }}</a></li>
                        <li><a href="{{ route('my-articles.index') }}"><i class="fa fa-book"></i> {{ __('messages.ui.menu_my_articles') }}</a></li>
                        <li><a href="{{ route('users.notifications', Auth::id()) }}"><i class="fa fa-bell"></i> {{ __('messages.ui.menu_notifications') }}</a></li>
                        <li><a href="{{ route('users.show_comments', Auth::id()) }}"><i class="fa fa-comment"></i> {{ __('messages.ui.menu_comments') }}</a></li>
                        <li><a href="{{ route('users.show_bookmarks', Auth::id()) }}"><i class="fa fa-heart"></i> {{ __('messages.ui.menu_following') }}</a></li>
                        <li><a href="{{ route('users.collections', Auth::id()) }}"><i class="fa fa-layer-group"></i> {{ __('messages.ui.menu_collections') }}</a></li>
                        <li><a href="{{ route('users.teams', Auth::id()) }}"><i class="fa fa-user-friends"></i> {{ __('messages.ui.menu_teams') }}</a></li>
                        <li><a href="{{ route('users.change_info') }}"><i class="fa fa-cog"></i> {{ __('messages.ui.menu_settings') }}</a></li>
                    </ul>
                    <div class="mobile-menu-label"><i class="fa fa-plus"></i> {{ __('messages.add.menu') }}</div>
                    <ul>
                        <li><a href="{{ route('my-articles.create') }}"><i class="fa fa-book"></i> {{ __('messages.add.book') }}</a></li>
                        <li><a href="{{ route('characters.create') }}"><i class="fa fa-user-pen"></i> {{ __('messages.add.character') }}</a></li>
                        <li><a href="{{ route('teams.create') }}"><i class="fa fa-user-friends"></i> {{ __('messages.add.team') }}</a></li>
                        <li><a href="{{ route('collections.create') }}"><i class="fa fa-layer-group"></i> {{ __('messages.add.collection') }}</a></li>
                    </ul>
                </div>
                {{-- Đăng xuất để RIÊNG ngoài dropdown, luôn hiện ở cuối --}}
                <ul class="mobile-menu-logout">
                    <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();"><i class="fa fa-sign-out"></i> {{ __('messages.ui.menu_logout') }}</a></li>
                </ul>
            @else
                <ul>
                    <li><a href="{{ route('login') }}"><i class="fa fa-sign-in"></i> {{ __('messages.auth.login') }}</a></li>
                    <li><a href="{{ route('register') }}"><i class="fa fa-user-plus"></i> {{ __('messages.auth.register') }}</a></li>
                </ul>
            @endauth
        </nav>
    </div>
    <script>
    (function () {
        var t = document.getElementById('mma-toggle');
        if (!t) return;
        function toggle(e) {
            if (e.target.closest('.mma-coins')) return;   // chừa link xu vẫn bấm được
            var open = t.classList.toggle('open');
            t.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        t.addEventListener('click', toggle);
        t.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(e); }
        });
    })();
    </script>

    {{-- Browse dropdown template used by tippy --}}
    <div class="templates-html">
        {{-- Submenu (cha-con) cho các mục header có con --}}
        @foreach(menu_items('header') as $hmi)
            @if(($hmi->activeChildren ?? collect())->count())
                <ul id="submenu-{{ $hmi->id }}" class="header-sublist">
                    @foreach($hmi->activeChildren as $c)
                        <li><a href="{{ $c->href }}"@if($c->target === '_blank') target="_blank"@endif>@if($c->icon)<i class="{{ $c->icon }}"></i> @endif{{ $c->display_label }}</a></li>
                    @endforeach
                </ul>
            @endif
        @endforeach

        <ul id="header-browse-list" class="header-sublist">
            @php $browseItems = menu_items('browse'); @endphp
            @if($browseItems->count())
                {{-- Admin đã cấu hình menu Browse --}}
                @foreach($browseItems as $mi)
                    <li><a href="{{ $mi->href }}"@if($mi->target === '_blank') target="_blank"@endif>{{ $mi->display_label }}</a></li>
                @endforeach
            @else
                {{-- Fallback: tự liệt kê tất cả thể loại --}}
                @foreach($navGenres as $genre)
                    <li><a href="{{ route('genres.show', $genre) }}">{{ $genre->name }}</a></li>
                @endforeach
                <hr>
                <li><a href="{{ route('catalog.index') }}">{{ __('messages.nav.all') }}</a></li>
            @endif
        </ul>

        @if(config('locales.user_multilingual', true) && config('locales.switchable', true))
        {{-- Language dropdown --}}
        <ul id="header-lang-list" class="header-sublist">
            @foreach(config('locales.supported', []) as $code => $loc)
                <li><a href="{{ route('locale.switch', $code) }}">
                    @if(!empty($loc['flag_code']))<span class="flag-icon flag-icon-{{ $loc['flag_code'] }}"></span> @endif{{ $loc['name'] }}
                </a></li>
            @endforeach
        </ul>
        @endif

        @auth
            {{-- Menu nút + (thêm) --}}
            <ul id="header-add-list" class="header-sublist">
                <li><a href="{{ route('my-articles.create') }}"><i class="fa fa-book"></i> {{ __('messages.add.book') }}</a></li>
                <li><a href="{{ route('characters.create') }}"><i class="fa fa-user-pen"></i> {{ __('messages.add.character') }}</a></li>
                <li><a href="{{ route('teams.create') }}"><i class="fa fa-user-friends"></i> {{ __('messages.add.team') }}</a></li>
                <li><a href="{{ route('collections.create') }}"><i class="fa fa-layer-group"></i> {{ __('messages.add.collection') }}</a></li>
            </ul>

            {{-- Menu avatar --}}
            <ul id="header-user-list" class="header-sublist">
                <li><a href="{{ route('users.show') }}"><i class="fa fa-user"></i> {{ __('messages.ui.menu_profile') }}</a></li>
                <li><a href="{{ route('my-articles.index') }}"><i class="fa fa-book"></i> {{ __('messages.ui.menu_my_articles') }}</a></li>
                <li><a href="#"><i class="fa fa-bell"></i> {{ __('messages.ui.menu_notifications') }}</a></li>
                <li><a href="{{ route('users.show_comments', Auth::id()) }}"><i class="fa fa-comment"></i> {{ __('messages.ui.menu_comments') }}</a></li>
                <li><a href="{{ route('users.show_bookmarks', Auth::id()) }}"><i class="fa fa-heart"></i> {{ __('messages.ui.menu_following') }}</a></li>
                <li><a href="#"><i class="fa fa-layer-group"></i> {{ __('messages.ui.menu_collections') }}</a></li>
                <li><a href="#"><i class="fa fa-user-friends"></i> {{ __('messages.ui.menu_teams') }}</a></li>
                <li><a href="{{ route('users.change_info') }}"><i class="fa fa-cog"></i> {{ __('messages.ui.menu_settings') }}</a></li>
                <hr>
                <li><a href="{{ route('users.transactions', Auth::id()) }}"><i class="fa fa-money-bill"></i> {{ __('messages.ui.menu_topup') }}</a></li>
                <hr>
                <li>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                        <i class="fa fa-sign-out"></i> {{ __('messages.ui.menu_logout') }}
                    </a>
                </li>
            </ul>
        @endauth
    </div>

    @auth
        <form id="logout-form-header" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
    @endauth
</div>

<script>
/* Force all lazy images to load immediately — src is already set by blade */
function forceLoadImages() {
    document.querySelectorAll('img.lazy-image').forEach(function(img) {
        var src = img.getAttribute('src') || img.getAttribute('data-src');
        if (src) { img.src = src; img.classList.remove('lazy-image'); }
    });
}
document.addEventListener('DOMContentLoaded', forceLoadImages);
/* Also run before swiper initializes */
window.addEventListener('load', forceLoadImages);
</script>
<script src="{{ asset('static/core/js/swiper.bundle.js') }}"></script>
<script src="{{ asset('static/core/js/popper.js') }}"></script>
<script src="{{ asset('static/core/js/tippy.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    window.CSRF_TOKEN = "{{ csrf_token() }}";
    window.DAILY_REWARD_CLAIMED = 0;
</script>
<script src="{{ asset('static/core/js/mainee8b.js') }}?ver={{ $assetVer('static/core/js/mainee8b.js') }}"></script>
<script src="{{ asset('static/core/js/site-effects.js') }}?ver={{ $assetVer('static/core/js/site-effects.js') }}"></script>
@yield('page_js')
@stack('scripts')
@include('client.partials.ads')
</body>
</html>
