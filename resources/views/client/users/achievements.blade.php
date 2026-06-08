@extends('client.users.profile')
@section('template_title', __('messages.account.nav_achievements'))

@section('user_content')
<h2>{{ __('messages.account.nav_achievements') }}</h2>
<div class="block"><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
    <i class="fa fa-award" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
    {{ __('messages.account.achievements_empty') }}
</div></div>
@endsection
