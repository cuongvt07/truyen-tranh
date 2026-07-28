<div class="navbar navbar-default navbar-static-top" role="navigation" id="nav">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">{{ __('messages.layout.show_menu') }}</span><span class="icon-bar"></span><span class="icon-bar"></span><span
                    class="icon-bar"></span>
            </button>
            @php
                $siteName = setting('site_name') ?: config('app.name', 'Laravel');
                $defaultLogo = setting('logo_file');
                $siteLogo = setting('logo_light_file') ?: $defaultLogo;
            @endphp
            <h1>
                <a class="header-logo" href="/" title="{{ $siteName }}">
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" style="max-height:50px;">
                    @else
                        {{ $siteName }}
                    @endif
                </a>
            </h1>
        </div>
        @include('client.partials.navbar')
    </div>

    <div class="navbar-breadcrumb">
        <div class="container breadcrumb-container">
            <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
                @if (!is_route('home.*'))
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="/"
                           accesskey="1"><span class="glyphicon glyphicon-home"></span></a><a href="/"
                                                                                              title="{{ __('messages.nav.home') }}"
                                                                                              itemprop="item"><span
                                itemprop="name">{{ __('messages.nav.home') }}</span></a>
                        <meta itemprop="position" content="1"/>
                    </li>
                @endif
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="{{ url()->current() }}" title="@yield('template_title')" itemprop="item"><span
                            itemprop="name">@yield('template_title')</span></a>
                    <meta itemprop="position" content="2"/>
                </li>
            </ol>
        </div>
    </div>
</div>
