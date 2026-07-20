@extends('layout.novelight')
@section('template_title', __('messages.account.banned_title'))

@section('content')
<div class="container">
    <div style="max-width:560px;margin:60px auto;text-align:center;padding:32px;border:1px solid var(--border,#2a2a3e);border-radius:10px;background:var(--bg-secondary,#1e1e2e)">
        <div style="font-size:48px;color:#e84040;margin-bottom:16px"><i class="fa fa-ban"></i></div>
        <h1 style="font-size:22px;margin-bottom:16px">{{ __('messages.account.banned_heading') }}</h1>
        <p class="meta-color" style="margin-bottom:8px">{{ __('messages.account.banned_expires', ['days' => $bannedUser->remaining_days]) }}</p>
        <p class="meta-color" style="margin-bottom:24px">{{ __('messages.account.banned_reason', ['reason' => $bannedUser->reason]) }}</p>
        <form method="POST" action="{{ route_path('logout') }}">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="fa fa-sign-out"></i> {{ __('messages.account.logout') }}</button>
        </form>
    </div>
</div>
@endsection
