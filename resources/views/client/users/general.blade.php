@extends('client.users.profile')
@section('template_title', __('messages.account.account_title', ['name' => $user->username]))

@section('user_content')
<div class="block">
    <h2>{{ __('messages.account.account_info') }}</h2>
    <div class="info-row">
        <div class="label">Username</div>
        <div class="detail">{!! method_exists($user,'renderUserName') ? $user->renderUserName() : e($user->username) !!}</div>
    </div>
    <div class="info-row">
        <div class="label">{{ __('messages.account.full_name') }}</div>
        <div class="detail">{{ $user->name }}</div>
    </div>
    <div class="info-row">
        <div class="label">Email</div>
        <div class="detail">{{ $user->email }}</div>
    </div>
    @if(!empty($user->gender_text))
    <div class="info-row">
        <div class="label">{{ __('messages.account.gender') }}</div>
        <div class="detail">{{ $user->gender_text }}</div>
    </div>
    @endif
    @if(!empty($user->date_of_birth_text))
    <div class="info-row">
        <div class="label">{{ __('messages.account.date_of_birth') }}</div>
        <div class="detail">{{ $user->date_of_birth_text }}</div>
    </div>
    @endif
    <div class="info-row">
        <div class="label">{{ __('messages.account.role') }}</div>
        <div class="detail">{!! method_exists($user,'renderRoleText') ? $user->renderRoleText() : '' !!}</div>
    </div>
    @if(isset($user->points))
    <div class="info-row">
        <div class="label">{{ __('messages.account.points') }}</div>
        <div class="detail"><i class="fa fa-coins"></i> {{ number_format($user->points) }}</div>
    </div>
    @endif
    @if(!empty($user->description))
    <div class="info-row">
        <div class="label">{{ __('messages.account.about') }}</div>
        <div class="detail">{!! nl2br(e($user->description)) !!}</div>
    </div>
    @endif
</div>
@endsection
