<header class="header">
    <div class="container">
        <div class="header__inner">
            <div id="header-mobile-btn" class="header-btn open-close" p-target="fullscreen-mobile-menu"
                 p-target-class="active" p-event="burgerMenuOpenClose"><i class="fa fa-bars"></i></div>
            @php
                $siteName = setting('site_name') ?: config('app.name');
                $defaultLogo = setting('logo_file');
                $siteLogoLight = setting('logo_light_file') ?: $defaultLogo;
                $siteLogoDark = setting('logo_dark_file') ?: $defaultLogo ?: $siteLogoLight;
                $siteLogoLight = $siteLogoLight ? asset('storage/' . $siteLogoLight) : '/static/core/images/alphanovel/alpha-app-icon.png';
                $siteLogoDark = $siteLogoDark ? asset('storage/' . $siteLogoDark) : $siteLogoLight;
            @endphp
            <a href="{{ route_path('home.index', []) }}" class="logo">
                <img src="{{ $siteLogoLight }}" alt="{{ $siteName }}" class="alpha-logo-img alpha-logo-img--light">
                <img src="{{ $siteLogoDark }}" alt="{{ $siteName }}" class="alpha-logo-img alpha-logo-img--dark" hidden>
            </a>
            <nav class="header-nav">
                <ul>
                    @forelse(menu_items('header') as $mi)
                        @include('partials.menu-header-item', ['mi' => $mi])
                    @empty
                        {{-- Fallback: nav mặc định khi chưa cấu hình menu --}}
                        <li><a href="{{ route_path('home.index', []) }}" class="header-btn no-link"><span class="alpha-nav-label">Discover</span></a></li>
                        <li class="header-nav__list"><div class="header-btn header-browse tippy-browse"><span class="alpha-nav-label">Novels</span> <i class="fa fa-caret-down"></i></div></li>
                        <li><a href="{{ auth()->check() ? route_path('users.reading_history', Auth::id()) : route_path('login', []) }}" class="header-btn no-link" @guest data-auth-open="login" @endguest><span class="alpha-nav-label">Library</span></a></li>
                        <li><a href="{{ route_path('pages.gifts', []) }}" class="header-btn no-link"><span class="alpha-nav-label">Gifts</span></a></li>
                        <li><a href="{{ route_path('my-articles.create', []) }}" class="header-btn no-link"><span class="alpha-nav-label">Writer</span></a></li>
                        <li><a href="{{ route_path('pages.blog', []) }}" class="header-btn no-link"><span class="alpha-nav-label">Blog</span></a></li>
                        <li><a href="#" id="open-live-search" class="header-btn no-link open-close" p-target="fullscreen-search"><span class="alpha-nav-label">Search</span></a></li>
                    @endforelse
                </ul>
            </nav>
            @php
                // Mục đang đứng -> tô màu primary. Mỗi mục khai báo các route
                // thuộc về nó, kể cả route con (vd Library gồm cả bookmarks,
                // collections...) để không bị mất highlight khi vào trang con.
                $navActive = fn (...$routes) => request()->routeIs(...$routes) ? ' is-active' : '';
            @endphp
            <nav class="alpha-header-nav">
                <ul>
                    <li><a href="{{ route_path('home.index', []) }}" class="header-btn no-link{{ $navActive('home.index') }}"><span class="alpha-nav-label">Discover</span></a></li>
                    <li class="header-nav__list"><div class="header-btn header-browse tippy-browse{{ $navActive('catalog.*', 'genres.*') }}"><span class="alpha-nav-label">Novels</span> <i class="fa fa-caret-down"></i></div></li>
                    <li><a href="{{ auth()->check() ? route_path('users.reading_history', Auth::id()) : route_path('login', []) }}" class="header-btn no-link{{ $navActive('users.reading_history', 'users.show_bookmarks', 'users.collections', 'users.favourites') }}" @guest data-auth-open="login" @endguest><span class="alpha-nav-label">Library</span></a></li>
                    <li><a href="{{ route_path('pages.gifts', []) }}" class="header-btn no-link{{ $navActive('pages.gifts') }}"><span class="alpha-nav-label">Gifts</span></a></li>
                    <li><a href="{{ route_path('my-articles.create', []) }}" class="header-btn no-link{{ $navActive('my-articles.*') }}"><span class="alpha-nav-label">Writer</span></a></li>
                    <li><a href="{{ route_path('pages.blog', []) }}" class="header-btn no-link{{ $navActive('pages.blog') }}"><span class="alpha-nav-label">Blog</span></a></li>
                    <li><a href="{{ route_path('home.search', []) }}" class="header-btn no-link{{ $navActive('home.search') }}"><span class="alpha-nav-label">Search</span></a></li>
                </ul>
            </nav>
            <div class="header-user">
                @auth
                    @php $authUser = Auth::user(); $userAvatar = $authUser->avatar ?: asset('static/account/images/no-ava.jpg'); @endphp

                    {{-- Nút + (thêm) — chỉ user đã từng mua gói (có lịch sử thanh toán) --}}
                    @if($authUser->hasPurchased())
                    <div id="add-item-btn" class="header-btn header-add"><i class="fa fa-plus"></i></div>
                    @endif

                    {{-- Chuông thông báo --}}
                    <a href="{{ route_path('users.notifications', Auth::id()) }}" class="header-btn header-bell" title="{{ __('messages.account.nav_notifications') }}">
                        <i class="fa fa-bell"></i>
                        @if(($unreadNotifCount ?? 0) > 0)<span class="notif-count">{{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}</span>@endif
                    </a>

                    {{-- Số xu --}}
                    <a href="{{ route_path('users.transactions', Auth::id()) }}" class="header-btn header-coins">
                        {{ number_format($authUser->points ?? 0) }}<i class="fa fa-coins"></i>
                    </a>

                    {{-- Avatar (mở menu) --}}
                    <div class="header-btn header-profile tippy-profile">
                        <div class="header-avatar {{ user_is_vip($authUser->id) ? 'vip-ring' : '' }}"><img src="{{ $userAvatar }}" alt="{{ $authUser->username }}">@include('partials.vip-crown', ['userId' => $authUser->id])</div>
                    </div>
                @else
                    <a href="{{ route_path('login', []) }}" class="btn login-btn" data-auth-open="login">{{ __('messages.auth.login') }}</a>
                    <a href="{{ route_path('register', []) }}" class="btn register-btn" data-auth-open="register">{{ __('messages.auth.register') }}</a>
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
                <button type="button" class="header-btn alpha-theme-toggle" aria-label="Theme switch" aria-pressed="false"></button>
            </div>
        </div>
    </div>
</header>
