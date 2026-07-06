@extends('layout.novelight')

@section('template_title', __('messages.auth.login'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/account/css/login.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <div class="container-login">
        <div id="login" class="login-form block">
            <h2>{{ __('messages.auth.login') }}</h2>

            @if(session('reading_limit_notice'))
                <div class="login-notice" style="color:#f0c040">
                    <p>{{ session('reading_limit_notice') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="login-notice" style="color:#f66">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <form method="post" action="{{ route('login') }}">
                @csrf

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-user"></i></div>
                    <input type="text" name="login" placeholder="{{ __('messages.auth.email_or_username') }}"
                           value="{{ old('login') }}" required autofocus>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-key"></i></div>
                    <input type="password" name="password" placeholder="{{ __('messages.auth.password') }}" required>
                </div>

                <div class="control-btn">
                    <a class="forgot-password-link" href="{{ route('password.request') }}">{{ __('messages.auth.forgot') }}</a>
                    <button class="btn btn-primary" type="submit">{{ __('messages.auth.login') }}</button>
                </div>
            </form>

            <hr>
            <div class="alternative">
                <a href="{{ route('auth.google') }}" class="btn btn-google"><i class="fab fa-google"></i><span> Google</span></a>
            </div>
        </div>

        <span class="account-span">{{ __('messages.auth.no_account') }} <a href="{{ route('register') }}">{{ __('messages.auth.register') }}</a></span>
    </div>
</div>
@endsection
