<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    @php
        $seoSep   = seo_setting('title_separator', ' · ');
        $seoSite  = html_entity_decode(seo_setting('site_name', config('app.name', __('messages.layout.default_site_name'))), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Title/description cấu hình riêng cho trang này trong Admin → SEO.
        // Có cấu hình thì nó thắng, vì đó chính là mục đích của phần cấu hình.
        $pageSeo  = \App\Support\PageSeo::current();
        $seoDesc  = $pageSeo['description']
            ?: (trim($__env->yieldContent('meta_description')) ?: seo_setting('default_description', __('messages.layout.default_meta_description')));
        $seoOg    = trim($__env->yieldContent('og_image')) ?: asset(ltrim(seo_setting('default_og_image', '/static/core/images/no_cover.webp'), '/'));
        // Tiêu đề trong DB có chỗ đã bị encode sẵn (&#039;), Blade escape thêm lần
        // nữa thành &amp;#039; -> giải mã trước khi ghép.
        $seoTitle = html_entity_decode(
            $pageSeo['title'] ?: trim($__env->yieldContent('template_title')),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
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
    @php $favicon = setting('favicon_file') ? asset('storage/' . setting('favicon_file')) : '/static/core/images/alphanovel/favicon-32x32.png'; @endphp
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="/static/core/images/alphanovel/alpha-app-icon.png">
    <meta name="theme-color" content="#4535ff">

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
    <link rel="stylesheet" href="{{ asset('static/core/css/alphanovel.css') }}?ver={{ $assetVer('static/core/css/alphanovel.css') }}">
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
        .header-user .header-btn { cursor: pointer; display: flex; align-items: center; gap: 5px; color: #fff; font-size: 17px; }
    </style>
    @include('client.partials.ad-head')
</head>
<body class="theme-alphanovel">
<script>
(function () {
    try {
        if (localStorage.getItem('alpha-theme-mode') === 'dark') {
            document.body.classList.add('alpha-dark');
        }
    } catch (e) {}
})();
</script>
@php
    // Cũng dùng lại ở footer bên dưới; partials.site-header tự định nghĩa riêng
    // để còn nhúng được vào trang đọc chương (không đi qua layout này).
    $siteName = setting('site_name') ?: config('app.name');
@endphp
@include('partials.site-header')

@php
    $bottomTabClass = 'LayoutTabs_item__jAMjf';
    $bottomTabActiveClass = ' LayoutTabs_item__active__7FHV6';
    $bottomLibraryUrl = auth()->check() ? route_path('users.reading_history', Auth::id()) : route_path('login', []);
    $bottomProfileUrl = auth()->check() ? route_path('users.show', []) : route_path('login', []);
    $bottomLibraryActive = request()->routeIs('users.show_bookmarks', 'users.reading_history', 'users.collections', 'users.favourites');
    $bottomProfileActive = request()->routeIs('users.show', 'users.show.profile', 'users.change_info', 'users.change_password', 'users.transactions', 'users.achievements', 'users.notifications');
@endphp
<nav data-testid="layout-tabs" class="LayoutTabs_wrapper__ZY5zg" aria-label="Mobile navigation">
    <a class="{{ $bottomTabClass }}{{ request()->routeIs('home.index') ? $bottomTabActiveClass : '' }}" href="{{ route_path('home.index', []) }}">
        <span class="AlphaIcon"><i class="fa fa-list"></i></span>
        Discover
    </a>
    <a class="{{ $bottomTabClass }}{{ request()->routeIs('catalog.*', 'articles.index') ? $bottomTabActiveClass : '' }}" href="{{ route_path('catalog.index', []) }}">
        <span class="AlphaIcon"><i class="fa fa-book"></i></span>
        Novels
    </a>
    <a class="{{ $bottomTabClass }}{{ request()->routeIs('home.search', 'catalog.live_search') ? $bottomTabActiveClass : '' }}" href="{{ route_path('home.search', []) }}">
        <span class="AlphaIcon"><i class="fa fa-search"></i></span>
        Search
    </a>
    <a class="{{ $bottomTabClass }}{{ $bottomLibraryActive ? $bottomTabActiveClass : '' }}" href="{{ $bottomLibraryUrl }}" @guest data-auth-open="login" @endguest>
        <span class="AlphaIcon"><i class="fa fa-heart"></i></span>
        Library
    </a>
    <a class="{{ $bottomTabClass }}{{ $bottomProfileActive ? $bottomTabActiveClass : '' }}" href="{{ $bottomProfileUrl }}" @guest data-auth-open="login" @endguest>
        <span class="AlphaIcon"><i class="fa fa-user"></i></span>
        Profile
    </a>
</nav>

<div class="page">
    <div class="content Layout_container__lyw0Z">
        @yield('content')

        @php
            // Footer luôn hiện đủ icon app/social; link nào chưa cấu hình thì trỏ tạm '#'.
            $appStoreUrl   = setting('app_store_url') ?: '#';
            $googlePlayUrl = setting('google_play_url') ?: '#';
            $socials = [
                'facebook'  => setting('social_facebook') ?: '#',
                'instagram' => setting('social_instagram') ?: '#',
                'tiktok'    => setting('social_tiktok') ?: '#',
                'reddit'    => setting('social_reddit') ?: '#',
                'quora'     => setting('social_quora') ?: '#',
                'medium'    => setting('social_medium') ?: '#',
                'youtube'   => setting('social_youtube') ?: '#',
            ];
            $footerContact = setting('footer_contact_email');
            $footerCompany = setting('footer_company');
        @endphp
        <footer class="alpha-site-footer" aria-label="{{ $siteName }} footer">
            <div class="alpha-site-footer__container">
                <div class="alpha-site-footer__body">
                    <div class="alpha-site-footer__group">
                        <span class="alpha-site-footer__title">{{ __('messages.foot.download_app') }}</span>
                        <nav class="alpha-site-footer__download" aria-label="Download app">
                            <a href="{{ $appStoreUrl }}" class="alpha-site-footer__link" aria-label="Download on the App Store" rel="noreferrer" target="_blank">
                                <span class="AlphaIcon"><svg width="22" height="28" viewBox="0 0 22 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18.375 14.8903C18.4019 12.7288 19.5229 10.6836 21.3013 9.55149C20.1794 7.89236 18.3002 6.84043 16.3451 6.77709C14.2599 6.55045 12.2384 8.06913 11.1758 8.06913C10.0926 8.06913 8.45655 6.79959 6.69481 6.83712C4.39845 6.91395 2.25766 8.26584 1.14044 10.3447C-1.26116 14.6502 0.530216 20.9778 2.83074 24.458C3.98175 26.1622 5.32694 28.0658 7.0871 27.9983C8.80951 27.9243 9.4528 26.861 11.5319 26.861C13.5917 26.861 14.1953 27.9983 15.9911 27.9553C17.8394 27.9243 19.0038 26.2436 20.1145 24.5233C20.9415 23.309 21.5778 21.967 22 20.5469C19.828 19.5956 18.3775 17.3323 18.375 14.8903Z" fill="currentColor"></path><path d="M14.9828 4.48832C15.9906 3.23566 16.487 1.62558 16.3668 0C14.8272 0.167441 13.4051 0.929367 12.3837 2.13397C11.385 3.31092 10.8652 4.89268 10.9635 6.45612C12.5036 6.47254 14.0177 5.73127 14.9828 4.48832Z" fill="currentColor"></path></svg></span>
                            </a>
                            <a href="{{ $googlePlayUrl }}" class="alpha-site-footer__link" aria-label="Get it on Google Play" rel="noreferrer" target="_blank">
                                <span class="AlphaIcon"><svg width="21" height="24" viewBox="0 0 21 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.4211 0.370606C0.156435 0.657999 0 1.10402 0 1.6816V22.3179C0 22.8964 0.156435 23.3415 0.4211 23.6289L0.488402 23.6961L11.7562 12.136V11.9997V11.8635L0.488402 0.30249L0.4211 0.370606Z" fill="#4285F4"></path><path d="M15.5092 15.9905L11.7539 12.1359V11.9997V11.8634L15.5101 8.00977L15.5947 8.05922L20.0449 10.6532C21.3155 11.3941 21.3155 12.6062 20.0449 13.348L15.5947 15.942L15.5092 15.9905Z" fill="#FBBC05"></path><path d="M15.5978 15.9411L11.7561 11.9998L0.421876 23.6289C0.840246 24.0842 1.53238 24.1402 2.31182 23.6867L15.5978 15.9411Z" fill="#EA4335"></path><path d="M15.5978 8.05826L2.31182 0.31359C1.53238 -0.140827 0.840246 -0.083908 0.421876 0.371442L11.757 12.0006L15.5978 8.05826Z" fill="#34A853"></path></svg></span>
                            </a>
                            <a href="{{ $appStoreUrl }}" rel="noreferrer" target="_blank" class="alpha-site-footer__install">{{ __('messages.foot.install_app') }}</a>
                        </nav>
                    </div>

                    <div class="alpha-site-footer__group">
                        <span class="alpha-site-footer__title">{{ __('messages.foot.follow_us') }}</span>
                        <nav class="alpha-site-footer__socials" aria-label="Social links">
                            @foreach($socials as $network => $url)
                                <a href="{{ $url }}" class="alpha-site-footer__link" aria-label="{{ $network }}" target="_blank" rel="nofollow noreferrer">
                                    <span class="AlphaIcon">@include('partials.social-icon', ['network' => $network])</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <nav class="alpha-site-footer__nav" aria-label="Footer navigation">
                        <a class="alpha-site-footer__link" href="{{ route_path('pages.blog', []) }}">{{ __('messages.foot.blog') }}</a>
                        <a class="alpha-site-footer__link" href="{{ route_path('pages.help', []) }}">{{ __('messages.foot.help') }}</a>
                        <a class="alpha-site-footer__link" href="{{ $footerContact ? 'mailto:'.$footerContact : route_path('pages.feedback', []) }}">{{ __('messages.foot.contact_us') }}</a>
                    </nav>
                </div>

                <div class="alpha-site-footer__bottom">
                    <div class="alpha-site-footer__copyright">
                        <span class="alpha-site-footer__text">{{ date('Y') }} &copy; All Rights Reserved.</span>
                        @if($footerCompany)
                            <span class="alpha-site-footer__text">{{ $footerCompany }}</span>
                        @else
                            <span class="alpha-site-footer__text">{{ $siteName }}</span>
                        @endif
                    </div>
                    <nav class="alpha-site-footer__nav" aria-label="Legal navigation">
                        <a class="alpha-site-footer__link" href="{{ route_path('pages.terms', []) }}">{{ __('messages.footer.terms') }}</a>
                        <a class="alpha-site-footer__link" href="{{ route_path('pages.rules', []) }}">{{ __('messages.foot.privacy_policy') }}</a>
                        <a class="alpha-site-footer__link" href="{{ route_path('pages.dmca', []) }}">{{ __('messages.footer.dmca') }}</a>
                    </nav>
                </div>
            </div>
        </footer>
    </div>

    {{-- Fullscreen search overlay --}}
    <div id="fullscreen-search" class="fullscreen hide">
        <div class="fullscreen-container">
            <form action="{{ route_path('catalog.index', []) }}" method="GET">
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
                    <li><a href="{{ route_path('home.index', []) }}"><i class="fa fa-home"></i> {{ __('messages.nav.home') }}</a></li>
                    <li><div class="tippy-browse"><span><i class="fa fa-layer-group"></i> {{ __('messages.nav.browse') }}</span> <i class="fa fa-caret-down"></i></div></li>
                    <li><a href="{{ route_path('pages.forum', []) }}"><i class="fa fa-comments"></i> {{ __('messages.nav.forum') }}</a></li>
                    <li><a href="{{ route_path('pages.help', []) }}"><i class="fa fa-question-circle"></i> {{ __('messages.nav.faq') }}</a></li>
                    <li><a href="{{ route_path('home.show_new_update_articles', []) }}"><i class="fa fa-bolt"></i> {{ __('messages.nav.new') }}</a></li>
                    <li><a href="{{ route_path('home.show_completed_articles', []) }}"><i class="fa fa-check-circle"></i> {{ __('messages.nav.completed') }}</a></li>
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
                        <a href="{{ route_path('users.transactions', Auth::id()) }}" class="mma-coins"><i class="fa fa-coins"></i> {{ number_format($mAuth->points ?? 0) }}</a>
                    </div>
                    <i class="fa fa-caret-down mma-caret"></i>
                </div>
                <div class="mma-collapse">
                    <ul>
                        <li><a href="{{ route_path('users.show', []) }}"><i class="fa fa-user"></i> {{ __('messages.ui.menu_profile') }}</a></li>
                        <li><a href="{{ route_path('my-articles.index', []) }}"><i class="fa fa-book"></i> {{ __('messages.ui.menu_my_articles') }}</a></li>
                        <li><a href="{{ route_path('users.notifications', Auth::id()) }}"><i class="fa fa-bell"></i> {{ __('messages.ui.menu_notifications') }}</a></li>
                        <li><a href="{{ route_path('users.show_comments', Auth::id()) }}"><i class="fa fa-comment"></i> {{ __('messages.ui.menu_comments') }}</a></li>
                        <li><a href="{{ route_path('users.show_bookmarks', Auth::id()) }}"><i class="fa fa-heart"></i> {{ __('messages.ui.menu_following') }}</a></li>
                        <li><a href="{{ route_path('users.collections', Auth::id()) }}"><i class="fa fa-layer-group"></i> {{ __('messages.ui.menu_collections') }}</a></li>
                        <li><a href="{{ route_path('users.change_info', []) }}"><i class="fa fa-cog"></i> {{ __('messages.ui.menu_settings') }}</a></li>
                    </ul>
                    {{-- Gọi thẳng Auth::user(): $authUser chỉ tồn tại trong
                         partials.site-header, không rò sang được view này. --}}
                    @if(auth()->user()->hasPurchased())
                    <div class="mobile-menu-label"><i class="fa fa-plus"></i> {{ __('messages.add.menu') }}</div>
                    <ul>
                        <li><a href="{{ route_path('my-articles.create', []) }}"><i class="fa fa-book"></i> {{ __('messages.add.book') }}</a></li>
                        <li><a href="{{ route_path('collections.create', []) }}"><i class="fa fa-layer-group"></i> {{ __('messages.add.collection') }}</a></li>
                    </ul>
                    @endif
                </div>
                {{-- Đăng xuất để RIÊNG ngoài dropdown, luôn hiện ở cuối --}}
                <ul class="mobile-menu-logout">
                    <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();"><i class="fa fa-sign-out"></i> {{ __('messages.ui.menu_logout') }}</a></li>
                </ul>
            @else
                <ul>
                    <li><a href="{{ route_path('login', []) }}" data-auth-open="login"><i class="fa fa-sign-in"></i> {{ __('messages.auth.login') }}</a></li>
                    <li><a href="{{ route_path('register', []) }}" data-auth-open="register"><i class="fa fa-user-plus"></i> {{ __('messages.auth.register') }}</a></li>
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

    @include('partials.site-header-menus')

    @guest
        @include('auth.drawer')
    @endguest

    @auth
        <form id="logout-form-header" method="POST" action="{{ route_path('logout', []) }}" style="display:none">@csrf</form>
        @php session()->pull('daily_checkin_prompt', false); @endphp
        @include('client.partials.daily-checkin')
    @endauth
</div>

@guest
<script>
(function () {
    var drawer = document.getElementById('alpha-auth-drawer');
    if (!drawer) return;

    var loginUrl = @json(route_path('login', []));
    var registerUrl = @json(route_path('register', []));
    var shouldAutoOpen = @json($errors->any() || session('reading_limit_notice') || session('status'));

    function setTab(tab) {
        tab = tab === 'register' ? 'register' : 'login';
        drawer.querySelectorAll('[data-auth-tab]').forEach(function (button) {
            button.classList.toggle('active', button.getAttribute('data-auth-tab') === tab);
        });
        drawer.querySelectorAll('[data-auth-pane]').forEach(function (pane) {
            pane.classList.toggle('active', pane.getAttribute('data-auth-pane') === tab);
        });
    }

    function openDrawer(tab) {
        setTab(tab || drawer.getAttribute('data-default-tab') || 'login');
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('alpha-auth-open');
        var focusTarget = drawer.querySelector('.alpha-auth-pane.active input');
        if (focusTarget) window.setTimeout(function () { focusTarget.focus(); }, 160);
    }

    function closeDrawer() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('alpha-auth-open');
    }

    drawer.querySelectorAll('[data-auth-tab]').forEach(function (button) {
        button.addEventListener('click', function () {
            setTab(button.getAttribute('data-auth-tab'));
        });
    });

    drawer.querySelectorAll('[data-auth-close]').forEach(function (button) {
        button.addEventListener('click', closeDrawer);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && drawer.classList.contains('is-open')) closeDrawer();
    });

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-auth-open]');
        if (!trigger) {
            var link = event.target.closest('a[href]');
            if (!link) return;
            var href = link.getAttribute('href') || '';
            var path;
            try {
                path = new URL(href, window.location.origin).pathname;
            } catch (e) {
                return;
            }
            if (path === loginUrl) trigger = { getAttribute: function () { return 'login'; } };
            if (path === registerUrl) trigger = { getAttribute: function () { return 'register'; } };
        }

        if (!trigger) return;
        event.preventDefault();
        openDrawer(trigger.getAttribute('data-auth-open'));
    });

    window.AlphaAuthDrawer = { open: openDrawer, close: closeDrawer, setTab: setTab };

    document.addEventListener('DOMContentLoaded', function () {
        if (shouldAutoOpen || location.pathname === loginUrl || location.pathname === registerUrl) {
            openDrawer(location.pathname === registerUrl ? 'register' : drawer.getAttribute('data-default-tab'));
        }
    });
})();
</script>
@endguest

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
<script>
function initAlphaSliders(root) {
    root = root || document;
    if (typeof Swiper === 'undefined') return;

    root.querySelectorAll('.alpha-slider.swiper-container:not([data-alpha-slider-ready])').forEach(function (el) {
        var type = el.getAttribute('data-alpha-slider') || 'books';
        var section = el.closest('section') || el.parentElement;
        var next = section ? section.querySelector('.alpha-slider-next') : null;
        var prev = section ? section.querySelector('.alpha-slider-prev') : null;
        var options = {
            slidesPerView: 3,
            spaceBetween: 16,
            loop: false,
            watchOverflow: true,
            navigation: next && prev ? { nextEl: next, prevEl: prev } : undefined,
            breakpoints: {
                768: { slidesPerView: 5, spaceBetween: 18 },
                1200: { slidesPerView: 9, spaceBetween: 24 }
            }
        };

        if (type === 'trending') {
            options.slidesPerView = 2;
            options.slidesPerGroup = 2;
            options.grid = { rows: 2, fill: 'row' };
            options.breakpoints = {
                768: { slidesPerView: 3, slidesPerGroup: 3, spaceBetween: 18, grid: { rows: 2, fill: 'row' } },
                1024: { slidesPerView: 4, slidesPerGroup: 4, spaceBetween: 24, grid: { rows: 2, fill: 'row' } }
            };
        }

        el.setAttribute('data-alpha-slider-ready', '1');
        new Swiper(el, options);
    });
}
document.addEventListener('DOMContentLoaded', function () { initAlphaSliders(document); });
window.addEventListener('load', function () { initAlphaSliders(document); });
</script>
<script src="{{ asset('static/core/js/popper.js') }}"></script>
<script src="{{ asset('static/core/js/tippy.js') }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    window.CSRF_TOKEN = "{{ csrf_token() }}";
    window.DAILY_REWARD_CLAIMED = 0;
</script>
@include('partials.site-header-theme')
<script src="{{ asset('static/core/js/mainee8b.js') }}?ver={{ $assetVer('static/core/js/mainee8b.js') }}"></script>
<script src="{{ asset('static/core/js/site-effects.js') }}?ver={{ $assetVer('static/core/js/site-effects.js') }}"></script>
@yield('page_js')
@stack('scripts')
@include('client.partials.ads')
@include('client.partials.ad-footer')
</body>
</html>
