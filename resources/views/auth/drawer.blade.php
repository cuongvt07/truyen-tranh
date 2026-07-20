@php
    $authDrawerTab = request()->routeIs('register') ? 'register' : 'login';
    if ($errors->any() && request()->routeIs('register')) {
        $authDrawerTab = 'register';
    }
@endphp

<div class="alpha-auth-drawer" id="alpha-auth-drawer" data-default-tab="{{ $authDrawerTab }}" aria-hidden="true">
    <div class="alpha-auth-drawer__shade" data-auth-close></div>
    <aside class="alpha-auth-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="alpha-auth-title">
        <button type="button" class="alpha-auth-drawer__close" data-auth-close aria-label="Close">
            <i class="fa fa-times"></i>
        </button>

        <div class="alpha-auth-drawer__brand">
            <span>{{ config('app.name') }}</span>
            <strong id="alpha-auth-title">Reader account</strong>
        </div>

        @if(session('reading_limit_notice'))
            <div class="alpha-alert alpha-alert--warning">{{ session('reading_limit_notice') }}</div>
        @endif
        @if(session('status'))
            <div class="alpha-alert alpha-alert--success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alpha-alert alpha-alert--danger">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="alpha-auth-tabs" role="tablist" aria-label="Authentication">
            <button type="button" data-auth-tab="login" class="active" role="tab">{{ __('messages.auth.login') }}</button>
            <button type="button" data-auth-tab="register" role="tab">{{ __('messages.auth.register') }}</button>
        </div>

        <section class="alpha-auth-pane active" data-auth-pane="login">
            <div class="alpha-auth-card__head">
                <h2>{{ __('messages.auth.login') }}</h2>
                <p>Access your saved novels, comments, gifts, and reading progress.</p>
            </div>

            <form method="post" action="{{ route_path('login') }}" class="alpha-form">
                @csrf
                <div class="alpha-field">
                    <label>{{ __('messages.auth.email_or_username') }}</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autocomplete="username" placeholder="Email or username">
                </div>
                <div class="alpha-field">
                    <label>{{ __('messages.auth.password') }}</label>
                    <input type="password" name="password" required autocomplete="current-password" placeholder="Password">
                </div>
                <div class="alpha-auth-options">
                    <label class="alpha-check">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route_path('password.request') }}">{{ __('messages.auth.forgot') }}</a>
                </div>
                <button class="alpha-btn alpha-btn--primary alpha-auth-submit" type="submit">{{ __('messages.auth.login') }}</button>
            </form>

            <div class="alpha-auth-alt">
                <span>or continue with</span>
                <a href="{{ route_path('auth.google') }}" class="alpha-btn alpha-auth-google"><i class="fab fa-google"></i> Google</a>
            </div>
        </section>

        <section class="alpha-auth-pane" data-auth-pane="register">
            <div class="alpha-auth-card__head">
                <h2>{{ __('messages.auth.register') }}</h2>
                <p>Start a synced library and keep every novel update in one account.</p>
            </div>

            <form method="post" action="{{ route_path('register') }}" class="alpha-form">
                @csrf
                <div class="alpha-form-grid alpha-form-grid--2">
                    <div class="alpha-field">
                        <label>{{ __('messages.auth.display_name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Display name">
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.auth.username') }}</label>
                        <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="Username">
                    </div>
                </div>
                <div class="alpha-field">
                    <label>{{ __('messages.auth.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email address">
                </div>
                <div class="alpha-form-grid alpha-form-grid--2">
                    <div class="alpha-field">
                        <label>{{ __('messages.auth.password') }}</label>
                        <input type="password" name="password" required autocomplete="new-password" placeholder="Password">
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('messages.auth.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm password">
                    </div>
                </div>
                <p class="alpha-auth-terms">{!! __('messages.auth.agree_terms', [
                    'terms' => '<a href="'.route_path('pages.terms').'">'.e(__('messages.footer.terms')).'</a>',
                    'rules' => '<a href="'.route_path('pages.rules').'">'.e(__('messages.footer.rules')).'</a>',
                ]) !!}</p>
                <button class="alpha-btn alpha-btn--primary alpha-auth-submit" type="submit">{{ __('messages.auth.register') }}</button>
            </form>

            <div class="alpha-auth-alt">
                <span>or sign up with</span>
                <a href="{{ route_path('auth.google') }}" class="alpha-btn alpha-auth-google"><i class="fab fa-google"></i> Google</a>
            </div>
        </section>
    </aside>
</div>
