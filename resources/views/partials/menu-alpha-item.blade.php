@php
    /*
     * Một mục của .alpha-header-nav, dựng từ menu admin cấu hình.
     * Giữ đúng markup của nav alpha (span.alpha-nav-label + class active),
     * khác với partials.menu-header-item vốn phục vụ nav cũ.
     */
    $href  = $mi->href;
    $label = $mi->display_label;
    $icon  = $mi->icon;
    $kids  = $mi->activeChildren ?? collect();

    // "Library" cần link động theo user; khách thì mở drawer đăng nhập.
    $isLibrary = strtolower(trim((string) $label)) === 'library';
    if ($isLibrary) {
        $href = auth()->check()
            ? route_path('users.reading_history', Auth::id())
            : route_path('login', []);
    }

    // Tô sáng mục đang đứng. Mỗi token/route nhận cả route con của nó.
    $active = match (true) {
        $href === '#browse'                 => request()->routeIs('catalog.*', 'genres.*'),
        $href === '#search'                 => request()->routeIs('home.search'),
        $isLibrary                          => request()->routeIs(
            'users.reading_history', 'users.show_bookmarks', 'users.collections', 'users.favourites'
        ),
        default                             => $href !== '#' && $href !== ''
            && rtrim(parse_url($href, PHP_URL_PATH) ?? '', '/') === rtrim(request()->getPathInfo(), '/'),
    };
    $activeClass = $active ? ' is-active' : '';
@endphp

@if($kids->count())
    {{-- Mục có con -> dropdown tippy, dùng lại template submenu-<id> sẵn có. --}}
    <li class="header-nav__list">
        <div class="header-btn tippy-submenu{{ $activeClass }}" data-submenu="submenu-{{ $mi->id }}">
            @if($icon)<i class="{{ $icon }}"></i> @endif<span class="alpha-nav-label">{{ $label }}</span> <i class="fa fa-caret-down"></i>
        </div>
    </li>
@elseif($href === '#browse')
    <li class="header-nav__list">
        <div class="header-btn header-browse tippy-browse{{ $activeClass }}">
            @if($icon)<i class="{{ $icon }}"></i> @endif<span class="alpha-nav-label">{{ $label }}</span> <i class="fa fa-caret-down"></i>
        </div>
    </li>
@elseif($href === '#search')
    <li>
        <a href="{{ route_path('home.search', []) }}" class="header-btn no-link{{ $activeClass }}">
            @if($icon)<i class="{{ $icon }}"></i> @endif<span class="alpha-nav-label">{{ $label }}</span>
        </a>
    </li>
@else
    <li>
        <a href="{{ $href }}" class="header-btn no-link{{ $activeClass }}"
           @if($mi->target === '_blank') target="_blank" @endif
           @if($isLibrary) @guest data-auth-open="login" @endguest @endif>
            @if($icon)<i class="{{ $icon }}"></i> @endif<span class="alpha-nav-label">{{ $label }}</span>
        </a>
    </li>
@endif
