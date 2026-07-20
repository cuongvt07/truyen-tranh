@extends('layout.novelight')

@section('template_title', __('Confirm password'))

@section('content')
<div class="alpha-auth-page">
    <div class="container">
        <div class="alpha-auth-shell">
            <section class="alpha-auth-copy">
                <small>Secure area</small>
                <h1>Confirm your password</h1>
                <p>This action needs one more password check before continuing.</p>
            </section>
            <section class="alpha-auth-card">
                <small>{{ config('app.name') }}</small>
                <h2>{{ __('Confirm password') }}</h2>

                @error('password')<div class="alpha-alert alpha-alert--danger">{{ $message }}</div>@enderror

                <form method="POST" action="{{ route_path('password.confirm') }}" class="alpha-form">
                    @csrf
                    <div class="alpha-field">
                        <label>{{ __('Password') }}</label>
                        <input type="password" name="password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="alpha-btn alpha-btn--primary">{{ __('Confirm') }}</button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
