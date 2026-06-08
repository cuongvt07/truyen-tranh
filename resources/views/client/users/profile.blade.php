@extends('layout.novelight')

@php
    $avatar = $user->avatar ?: asset('static/account/images/no-ava.jpg');
    $panelBg = $user->background ?: $avatar;
    $isMine = isMyAccount($currentUser ?? null, $user);
@endphp

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/account/css/login.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="page-panel" style="background-image: url('{{ $panelBg }}');">
    <span class="background"></span>
</div>

<div class="container">
    <header class="header-user-page has-background">
        <div class="profile-avatar image image-cover lazy-load-bg">
            <img class="lazy-image" loading="eager" src="{{ $avatar }}" alt="{{ $user->username }}">
        </div>
        <h1 class="nickname">
            {!! method_exists($user, 'renderUserName') ? $user->renderUserName() : e($user->username) !!}
            @if(!empty($activeVipDays) && $activeVipDays > 0)
                <div class="profile-badges">
                    <div class="profile-badge"><span class="vip-badge"><i class="fa fa-crown"></i> VIP</span></div>
                </div>
            @endif
        </h1>
    </header>

    <div class="flex-content">
        {{-- Sidebar nav (full novelight set) --}}
        <div class="second-information">
            <div class="block user-nav">
                <a href="{{ route('users.show', $user->id) }}" class="btn {{ request()->routeIs('users.show','users.show.profile','users.show_posted_articles') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-list"></i> {{ __('messages.account.nav_profile') }}
                </a>
                <a href="{{ route('users.collections', $user->id) }}" class="btn {{ request()->routeIs('users.collections') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-layer-group"></i> {{ __('messages.account.nav_collections') }}
                </a>
                <a href="{{ route('users.teams', $user->id) }}" class="btn {{ request()->routeIs('users.teams') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-user-friends"></i> {{ __('messages.account.nav_teams') }}
                </a>
                <a href="{{ route('users.favourites', $user->id) }}" class="btn {{ request()->routeIs('users.favourites') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-heart"></i> {{ __('messages.account.nav_following') }}
                </a>
                <a href="{{ route('users.achievements', $user->id) }}" class="btn {{ request()->routeIs('users.achievements') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-award"></i> {{ __('messages.account.nav_achievements') }}
                </a>
                <a href="{{ route('users.suggestions', $user->id) }}" class="btn {{ request()->routeIs('users.suggestions') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-lightbulb"></i> {{ __('messages.account.nav_suggestions') }}
                </a>
                <a href="{{ route('users.show_bookmarks', $user->id) }}" class="btn {{ request()->routeIs('users.show_bookmarks') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-bookmark"></i> {{ __('messages.account.nav_bookmarks') }}
                </a>
                <a href="{{ route('users.reading_history', $user->id) }}" class="btn {{ request()->routeIs('users.reading_history') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-history"></i> Reading History
                </a>
                <a href="{{ route('users.show_comments', $user->id) }}" class="btn {{ request()->routeIs('users.show_comments') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-comment"></i> {{ __('messages.account.nav_comments') }}
                </a>
                <a href="{{ route('users.notifications', $user->id) }}" class="btn {{ request()->routeIs('users.notifications') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-bell"></i> {{ __('messages.account.nav_notifications') }}
                </a>
                <a href="{{ route('users.transactions', $user->id) }}" class="btn {{ request()->routeIs('users.transactions') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-money-bill"></i> {{ __('messages.account.nav_transactions') }}
                </a>
                <a href="{{ route('users.banlist', $user->id) }}" class="btn {{ request()->routeIs('users.banlist') ? '' : 'btn-invincible' }}">
                    <i class="fa fa-ban"></i> {{ __('messages.account.nav_banlist') }}
                </a>
                @if($isMine)
                    <a href="{{ route('users.change_info') }}" class="btn {{ request()->routeIs('users.change_info','users.change_password') ? '' : 'btn-invincible' }}">
                        <i class="fa fa-cog"></i> {{ __('messages.account.nav_settings') }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Main content --}}
        <div class="main">
            @yield('user_content')
        </div>
    </div>
</div>

<style>
.header-user-page { display:flex; align-items:flex-end; gap:20px; padding:30px 0 20px; position:relative; }
.header-user-page .profile-avatar { width:110px; height:110px; border-radius:50%; overflow:hidden; flex-shrink:0; border:4px solid var(--bg,#fff); box-shadow:0 4px 14px rgba(0,0,0,.25); }
.header-user-page .profile-avatar img { width:100%; height:100%; object-fit:cover; }
.header-user-page .nickname { font-size:28px; display:flex; align-items:center; gap:12px; }
.vip-badge { background:linear-gradient(90deg,#f0a020,#e8c040); color:#222; font-size:13px; padding:3px 10px; border-radius:20px; font-weight:700; }
.user-nav { display:flex; flex-direction:column; gap:8px; }
.user-nav .btn { text-align:left; justify-content:flex-start; }
.user-page-table { width:100%; border-collapse:collapse; }
.user-page-table th, .user-page-table td { padding:10px 12px; text-align:left; border-bottom:1px solid var(--border,#eee); font-size:14px; }
.info-row { display:flex; padding:10px 0; border-bottom:1px solid var(--border,#eee); }
.info-row .label { width:160px; color:var(--meta-color,#888); font-weight:500; }
.user-list-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:14px; }
.user-list-grid .item .poster { height:200px; border-radius:6px; overflow:hidden; }
.user-list-grid .item .poster img { width:100%; height:100%; object-fit:cover; }
.user-list-grid .item .title { font-size:13px; font-weight:500; margin-top:6px; }
</style>
@endsection
