@extends('client.users.profile')
@section('template_title', __('messages.account.nav_following'))

@section('user_content')
<div class="block list-names" style="margin-bottom:14px">
    <h2 class="user-tab-title">{{ __('messages.account.nav_following') }}</h2>
    <button class="btn" type="button">{{ __('messages.account.filter_all') }}</button>
    <button class="btn btn-invincible" type="button">{{ __('messages.account.filter_characters') }}</button>
    <button class="btn btn-invincible" type="button">{{ __('messages.account.filter_users') }}</button>
</div>
<div class="block">
    <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
        <i class="fa fa-heart" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
        {{ __('messages.account.following_empty') }}
    </div>
</div>
@endsection
