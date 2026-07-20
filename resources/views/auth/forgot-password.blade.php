@extends('layout.novelight')

@section('template_title', __('Forgot password'))

@section('content')
<div class="alpha-auth-page">
    <div class="container">
        <div class="alpha-auth-shell">
            <section class="alpha-auth-copy">
                <small>Account recovery</small>
                <h1>Reset your password</h1>
                <p>Enter your email address and we will send a password reset link.</p>
            </section>
            <section class="alpha-auth-card">
                <small>{{ config('app.name') }}</small>
                <h2>{{ __('Forgot password') }}</h2>

                @if(session('status'))<div class="alpha-alert alpha-alert--success">{{ session('status') }}</div>@endif
                @if($errors->any())<div class="alpha-alert alpha-alert--danger">{{ $errors->first('email') }}</div>@endif

                <form action="{{ route_path('password.email') }}" method="POST" class="alpha-form">
                    @csrf
                    <div class="alpha-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="example@email.com" required>
                    </div>
                    <button type="submit" class="alpha-btn alpha-btn--primary">{{ __('Confirm') }}</button>
                </form>

                <div class="alpha-auth-switch">
                    <a href="{{ route_path('login') }}">{{ __('messages.auth.login') }}</a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
