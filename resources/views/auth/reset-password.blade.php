@extends('layout.novelight')

@section('template_title', __('Reset password'))

@section('content')
<div class="alpha-auth-page">
    <div class="container">
        <div class="alpha-auth-shell">
            <section class="alpha-auth-copy">
                <small>Account recovery</small>
                <h1>Choose a new password</h1>
                <p>Use a strong password to protect your reading account and wallet.</p>
            </section>
            <section class="alpha-auth-card">
                <small>{{ config('app.name') }}</small>
                <h2>{{ __('Reset password') }}</h2>

                @if($errors->any())
                    <div class="alpha-alert alpha-alert--danger">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route_path('password.store') }}" class="alpha-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route_path('token') }}">
                    <div class="alpha-field">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" readonly>
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('New password') }}</label>
                        <input type="password" name="password" required autocomplete="new-password">
                    </div>
                    <div class="alpha-field">
                        <label>{{ __('Confirm password') }}</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="alpha-btn alpha-btn--primary">{{ __('Reset password') }}</button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
