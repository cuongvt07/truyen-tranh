@extends('layout.novelight')

@section('template_title', __('Verify email'))

@section('content')
<div class="alpha-auth-page">
    <div class="container">
        <div class="alpha-auth-shell">
            <section class="alpha-auth-copy">
                <small>Email verification</small>
                <h1>Check your inbox</h1>
                <p>Verify your email address to finish account setup and keep posting features available.</p>
            </section>
            <section class="alpha-auth-card">
                <small>{{ config('app.name') }}</small>
                <h2>{{ __('Verify email') }}</h2>

                @if (session('status') == 'verification-link-sent')
                    <div class="alpha-alert alpha-alert--success">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <p class="meta-color" style="line-height:1.7">
                    {{ __('Thanks for signing up. Before getting started, please verify your email address by clicking the link we just emailed to you. If you did not receive the email, we will gladly send you another.') }}
                </p>

                <div class="alpha-form-actions">
                    <form method="POST" action="{{ route_path('verification.send') }}">
                        @csrf
                        <button type="submit" class="alpha-btn alpha-btn--primary">{{ __('Resend verification email') }}</button>
                    </form>
                    <form method="POST" action="{{ route_path('logout') }}">
                        @csrf
                        <button type="submit" class="alpha-btn">{{ __('Log out') }}</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
