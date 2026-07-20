@extends('layout.novelight')

@section('template_title', __('messages.auth.register'))

@section('content')
<div class="alpha-auth-page">
    <div class="container">
        <div class="alpha-auth-shell">
            <section class="alpha-auth-copy">
                <div class="alpha-auth-copy__content">
                    <small>Join readers</small>
                    <h1>Create your story library</h1>
                    <p>Register to save novels, post comments, follow updates, and build your reading list.</p>
                    <div class="alpha-auth-metrics">
                        <span><b>Save</b> favorites</span>
                        <span><b>Review</b> novels</span>
                        <span><b>Unlock</b> chapters</span>
                    </div>
                </div>
                <div class="alpha-auth-visual">
                    <img src="{{ asset('static/core/images/alphanovel/heroes-with-app.png') }}" alt="Read novels online" loading="lazy">
                </div>
            </section>

            <section class="alpha-auth-card">
                <div class="alpha-auth-card__head">
                    <small>{{ config('app.name') }}</small>
                    <h2>{{ __('messages.auth.register') }}</h2>
                    <p>Start a synced library and keep every novel update in one account.</p>
                </div>

                @if($errors->any())
                    <div class="alpha-alert alpha-alert--danger">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                <form method="post" action="{{ route_path('register') }}" class="alpha-form">
                    @csrf
                    <div class="alpha-form-grid alpha-form-grid--2">
                        <div class="alpha-field">
                            <label>{{ __('messages.auth.display_name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Display name">
                        </div>
                        <div class="alpha-field">
                            <label>{{ __('messages.auth.username') }}</label>
                            <input type="text" name="username" value="{{ old('username') }}" required placeholder="Username">
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
                <div class="alpha-auth-switch">
                    {{ __('messages.auth.have_account') }} <a href="{{ route_path('login') }}">{{ __('messages.auth.login') }}</a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
