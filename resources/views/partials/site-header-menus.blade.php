@php
    // Dropdown thể loại header: CHỈ thể loại Hot. Chưa tick Hot cái nào -> không liệt kê genre (chỉ còn link "Tất cả").
    $navGenres = \App\Models\Genre::hot()->orderBy('name')->get();
@endphp
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
                {{-- Chỉ liệt kê thể loại Hot (admin tick). Chưa có Hot -> chỉ hiện link Tất cả. --}}
                @foreach($navGenres as $genre)
                    <li><a href="{{ route_path('genres.show', $genre) }}">{{ $genre->name }}</a></li>
                @endforeach
                @if($navGenres->isNotEmpty())<hr>@endif
                <li><a href="{{ route_path('catalog.index', []) }}">{{ __('messages.nav.all') }}</a></li>
            @endif
        </ul>

        @if(config('locales.user_multilingual', true) && config('locales.switchable', true))
        {{-- Language dropdown --}}
        <ul id="header-lang-list" class="header-sublist">
            @foreach(config('locales.supported', []) as $code => $loc)
                <li><a href="{{ route_path('locale.switch', $code) }}">
                    @if(!empty($loc['flag_code']))<span class="flag-icon flag-icon-{{ $loc['flag_code'] }}"></span> @endif{{ $loc['name'] }}
                </a></li>
            @endforeach
        </ul>
        @endif

        @auth
            {{-- Menu nút + (thêm) — chỉ user đã từng mua gói --}}
            @if(auth()->user()->hasPurchased())
            <ul id="header-add-list" class="header-sublist">
                <li><a href="{{ route_path('my-articles.create', []) }}"><i class="fa fa-book"></i> {{ __('messages.add.book') }}</a></li>
                <li><a href="{{ route_path('collections.create', []) }}"><i class="fa fa-layer-group"></i> {{ __('messages.add.collection') }}</a></li>
            </ul>
            @endif

            {{-- Menu avatar --}}
            <ul id="header-user-list" class="header-sublist">
                <li><a href="{{ route_path('users.show', []) }}"><i class="fa fa-user"></i> {{ __('messages.ui.menu_profile') }}</a></li>
                <li><a href="{{ route_path('my-articles.index', []) }}"><i class="fa fa-book"></i> {{ __('messages.ui.menu_my_articles') }}</a></li>
                <li><a href="{{ route_path('users.notifications', Auth::id()) }}"><i class="fa fa-bell"></i> {{ __('messages.ui.menu_notifications') }}</a></li>
                <li><a href="{{ route_path('users.show_comments', Auth::id()) }}"><i class="fa fa-comment"></i> {{ __('messages.ui.menu_comments') }}</a></li>
                <li><a href="{{ route_path('users.show_bookmarks', Auth::id()) }}"><i class="fa fa-heart"></i> {{ __('messages.ui.menu_following') }}</a></li>
                <li><a href="{{ route_path('users.collections', Auth::id()) }}"><i class="fa fa-layer-group"></i> {{ __('messages.ui.menu_collections') }}</a></li>
                <li><a href="{{ route_path('users.change_info', []) }}"><i class="fa fa-cog"></i> {{ __('messages.ui.menu_settings') }}</a></li>
                <hr>
                <li><a href="{{ route_path('users.transactions', Auth::id()) }}"><i class="fa fa-money-bill"></i> {{ __('messages.ui.menu_topup') }}</a></li>
                <hr>
                <li>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();">
                        <i class="fa fa-sign-out"></i> {{ __('messages.ui.menu_logout') }}
                    </a>
                </li>
            </ul>
        @endauth
    </div>
