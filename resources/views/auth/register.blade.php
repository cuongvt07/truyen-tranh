@extends('layout.novelight')

@section('template_title', __('messages.auth.register'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/account/css/login.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <div class="container-login">
        <div id="login" class="login-form block">
            <h2>{{ __('messages.auth.register') }}</h2>

            @if($errors->any())
                <div class="login-notice" style="color:#f66">
                    @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
                </div>
            @endif

            <form method="post" action="{{ route('register') }}">
                @csrf

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-id-card"></i></div>
                    <input type="text" name="name" placeholder="{{ __('messages.auth.display_name') }}" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-user"></i></div>
                    <input type="text" name="username" placeholder="{{ __('messages.auth.username') }}" value="{{ old('username') }}" required>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-envelope"></i></div>
                    <input type="email" name="email" placeholder="{{ __('messages.auth.email') }}" value="{{ old('email') }}" required>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-key"></i></div>
                    <input type="password" name="password" placeholder="{{ __('messages.auth.password') }}" required>
                </div>

                <div class="text-input">
                    <div class="left-icon"><i class="fa fa-lock"></i></div>
                    <input type="password" name="password_confirmation" placeholder="{{ __('messages.auth.confirm_password') }}" required>
                </div>

                <div class="control-btn">
                    <span></span>
                    <button class="btn btn-primary" type="submit">{{ __('messages.auth.register') }}</button>
                </div>
            </form>

            <hr>
            <div class="alternative">
                <a href="{{ route('auth.google') }}" class="btn btn-google"><i class="fab fa-google"></i><span> Google</span></a>
            </div>
            <p style="font-size:12px;margin-top:10px">{!! __('messages.auth.agree_terms', [
                'terms' => '<a href="'.route('pages.terms').'">'.e(__('messages.footer.terms')).'</a>',
                'rules' => '<a href="'.route('pages.rules').'">'.e(__('messages.footer.rules')).'</a>',
            ]) !!}</p>
        </div>

        <span class="account-span">{{ __('messages.auth.have_account') }} <a href="{{ route('login') }}">{{ __('messages.auth.login') }}</a></span>
    </div>
</div>
@endsection
