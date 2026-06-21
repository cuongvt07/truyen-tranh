<div class="navbar-collapse collapse" itemscope itemtype="https://schema.org/WebSite">
    <meta itemprop="url" content="/"/>
    <ul class="control nav navbar-nav">
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                <span class="glyphicon glyphicon-list"></span> {{ __('messages.nav.category') }} <span class="caret"></span>
            </a>
            <ul class="dropdown-menu" role="menu">
                @foreach ($links as $link)
                    <li><a href="{{ $link->link }}" title="{{ $link->name }}">{{ $link->name }}</a></li>
                @endforeach
            </ul>
        </li>
        <li class="dropdown">
            @php
                $SIZE = 16;
            @endphp

            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                <span class="glyphicon glyphicon-list"></span> {{ __('messages.common.genres') }} <span class="caret"></span>
            </a>
            <div class="dropdown-menu multi-column">
                <div class="row">
                    @foreach (array_chunk($genres->toArray(), $SIZE) as $chunk)
                        <div class="col-md-4">
                            <ul class="dropdown-menu">
                                @foreach ($chunk as $genre)
                                    <li><a href="{{ route('genres.show', $genre['slug']['slug'] ?? $genre['id']) }}"
                                           title="{{ $genre['name'] }}">{{ $genre['name'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </li>
        <li class="dropdown">
            @if (!$isUserLoggedIn)
                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                    <span class="glyphicon glyphicon-user"></span> {{ __('messages.nav.account') }} <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">

                    <li><a href="{{ route('login') }}" title="{{ __('messages.auth.login') }}">{{ __('messages.auth.login') }}</a></li>
                    <li><a href="{{ route('register') }}" title="{{ __('messages.auth.register') }}">{{ __('messages.auth.register') }}</a></li>
                    <li><a href="{{ route('client.paypoints') }}" title="{{ __('messages.nav.topup') }}">{{ __('messages.nav.topup') }}</a></li>
                </ul>
            @else
                <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                    <span class="glyphicon glyphicon-user"></span>
                    {!! $currentUser->renderUserName() !!}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                    @if ($currentUser->is_admin)
                        <li>
                            <a href="{{ route('admin.dashboard') }}" title="{{ __('messages.nav.admin_panel') }}"><i class="fa fa-cog"
                                                                                            aria-hidden="true"></i>
                                {{ __('messages.nav.admin_panel') }}</a>
                        </li>
                    @endif
                    @if($currentUser->is_poster || $currentUser->is_admin)
                        <li><a href="{{ route('admin.articles.create') }}" title="{{ __('messages.nav.add_article') }}"><i
                                    class="fa fa-plus"
                                    aria-hidden="true"></i>
                                {{ __('messages.nav.add_article') }}</a>
                        </li>
                    @endif
                    @if($currentUser->is_admin)
                        <li>
                            <a href="{{ route('admin.authors.create') }}" title="{{ __('messages.nav.add_author') }}"><i class="fa fa-plus"
                                                                                                  aria-hidden="true"></i>
                                {{ __('messages.nav.add_author') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.genres.create') }}" title="{{ __('messages.nav.add_genre') }}"><i class="fa fa-plus"
                                                                                                  aria-hidden="true"></i>
                                {{ __('messages.nav.add_genre') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.menus.create') }}" title="{{ __('messages.nav.add_link') }}"><i class="fa fa-plus"
                                                                                             aria-hidden="true"></i>
                                {{ __('messages.nav.add_link') }}</a>
                        </li>
                    @endif
                    <li><a href="{{ route('users.show', $currentUser->id) }}" title="{{ __('messages.nav.account_info') }}"><i
                                class="fa fa-solid fa-circle-info"></i>
                            {{ __('messages.nav.account_info') }}</a></li>
                    <li><a href="{{ route('client.paypoints') }}" title="{{ __('messages.pay.topup_and_buy_vip') }}"><i
                                class="fa fa-solid fa-credit-card"></i>
                            {{ __('messages.pay.topup_and_buy_vip') }}</a></li>
                    <li><a href="{{ route('users.show_bookmarks', $currentUser->id) }}" title="Bookmark"><i
                                class="fa fa-solid fa-bookmark"></i> Bookmark</a></li>
                    <li><a href="{{ route('users.show_posted_articles', $currentUser->id) }}"
                           title="{{ __('messages.nav.posted_articles') }}"><i
                                class="fa fa-list"></i> {{ __('messages.nav.posted_articles') }}</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="post" id="logout">
                            @csrf
                            <button type="submit"><i class="fa fa-sign-out"></i> {{ __('messages.auth.logout') }}</button>
                        </form>
                    </li>
                </ul>
            @endif
        </li>
        @if ($isUserLoggedIn)
            <li>
            <a href="{{ route('client.paypoints') }}">
                <i class="fa fa-database"></i>
                {{ __('messages.nav.coin_balance') }} <strong>{{ number_format($currentUser->points) }}</strong>
            </a>
            </li>
        @endif
        @if ($isUserLoggedIn && $currentUser && $activeVipDays > 0)
            <li>
                <a href="javascript:void(0)">
                    <i class="fa fa-clock"></i>
                    {{ __('messages.nav.vip_package') }}
                    <strong id="package-countdown"></strong>
                </a>
            </li>
            <script>
                const countdownElement = document.getElementById('package-countdown');
                let remainingTime = {{ $activeVipDays * 24 * 3600 }};

                function updateCountdown() {
                    if (remainingTime > 0) {
                        const hours = Math.floor(remainingTime / 3600);
                        const minutes = Math.floor((remainingTime % 3600) / 60);
                        const seconds = remainingTime % 60;
                        countdownElement.textContent = `${hours}h ${minutes}m ${seconds}s`;
                        remainingTime--;
                    } else {
                        countdownElement.textContent = @json(__('messages.nav.expired'));
                    }
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            </script>
        @endif
    </ul>

    <form class="navbar-form navbar-right" role="search" action="{{ route('home.search') }}">
        @if($isUserLoggedIn && !$currentUser->hasVerifiedEmail())
            <a href="{{ route('verification.notice') }}" class="btn btn-danger">{{ __('messages.nav.verify_email_cta') }}</a>
        @endif
        <div class="input-group search-holder">
            <input aria-label="{{ __('messages.nav.keyword_search') }}" class="form-control" type="search" name="keyword"
                   placeholder="{{ __('messages.nav.search_placeholder') }}" value="" itemprop="query-input" required
                   style="border-radius: 5px 0 0 5px; height: 40px;"/>
            <div class="input-group-btn">
                <button class="btn btn-default" type="submit" aria-label="{{ __('messages.nav.search') }}"
                        style="border-radius: 0 5px 5px 0; height: 40px; background: #e7e7e7;">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
            </div>
        </div>
        <div class="list-group list-search-res hide"></div>
    </form>
</div>
