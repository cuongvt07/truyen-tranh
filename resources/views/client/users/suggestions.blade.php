@extends('client.users.profile')
@section('template_title', __('messages.account.nav_suggestions'))

@section('user_content')
<div class="block">
    <h2 class="user-tab-title">{{ __('messages.account.nav_suggestions') }}</h2><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
    <i class="fa fa-lightbulb" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
    {{ __('messages.account.suggestions_empty') }}
</div></div>
@endsection
