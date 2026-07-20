@extends('layout.novelight')

@section('template_title', __('messages.auth.login'))

@section('content')
<div class="alpha-auth-page">
    <div class="container">
        <div class="alpha-auth-shell">
            <section class="alpha-auth-copy">
                <div class="alpha-auth-copy__content">
                    <small>Reader account</small>
                    <h1>Continue your library</h1>
                    <p>Sign in to keep reading, save books, unlock chapters, and follow new updates.</p>
                    <div class="alpha-auth-metrics">
                        <span><b>10K+</b> novels</span>
                        <span><b>24/7</b> reading</span>
                        <span><b>Sync</b> library</span>
                    </div>
                </div>
                <div class="alpha-auth-visual">
                    <img src="{{ asset('static/core/images/alphanovel/heroes-with-app.png') }}" alt="Read novels online" loading="lazy">
                </div>
            </section>

            <section class="alpha-auth-card">
                <div class="alpha-auth-card__head">
                    <small>{{ config('app.name') }}</small>
                    <h2>{{ __('messages.auth.login') }}</h2>
                    <p>Access your saved novels, comments, gifts, and reading progress.</p>
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

                <form method="post" action="{{ route_path('login') }}" class="alpha-form">
                    @csrf
                    <div class="alpha-field">
                        <label>{{ __('messages.auth.email_or_username') }}</label>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="Email or username">
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
                <div class="alpha-auth-switch">
                    {{ __('messages.auth.no_account') }} <a href="{{ route_path('register') }}">{{ __('messages.auth.register') }}</a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
