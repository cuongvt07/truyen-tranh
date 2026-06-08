@extends('client.users.profile')
@section('template_title', __('messages.account.nav_notifications'))

@section('user_content')
<h2>{{ __('messages.account.nav_notifications') }}</h2>
<div class="block">
    <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
        <i class="fa fa-bell" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
        {{ __('messages.account.notifications_empty') }}
    </div>
</div>
@endsection
