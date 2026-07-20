@extends('client.users.profile')
@section('template_title', __('messages.account.change_password'))

@section('user_content')
<div class="block">
    <h2 class="user-tab-title">{{ __('messages.account.change_password') }}</h2>

    @if($message = session('status'))
        <div class="alert-success" style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ $message }}</div>
    @endif

    <form action="{{ route_path('password.update') }}" method="post" class="auth-form">
        @csrf @method('put')

        <div class="text-input">
            <label>{{ __('messages.account.current_password') }}</label>
            <input type="password" name="current_password" required>
            @error('current_password')<span style="color:#f88;font-size:12px">{{ $message }}</span>@enderror
        </div>
        <div class="text-input">
            <label>{{ __('messages.account.new_password') }}</label>
            <input type="password" name="password" required>
            @error('password')<span style="color:#f88;font-size:12px">{{ $message }}</span>@enderror
        </div>
        <div class="text-input">
            <label>{{ __('messages.account.confirm_new_password') }}</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.account.change_password') }}</button>
    </form>
</div>

<style>
.auth-form .text-input { margin-bottom:14px; }
.auth-form .text-input label { display:block; font-size:13px; margin-bottom:4px; color:var(--meta-color,#999); }
.auth-form .text-input input { width:100%; max-width:400px; padding:9px 12px; border-radius:4px; border:1px solid var(--border,#2a2a3e); background:var(--bg,#131320); color:inherit; font-size:14px; }
</style>
@endsection
