@extends('client.users.profile')
@section('template_title', __('messages.account.nav_teams'))

@section('user_content')
@php
    $teamRoleLabels = [
        'leader' => __('messages.community.role_leader'),
        'admin' => __('messages.community.role_admin'),
        'editor' => __('messages.community.role_editor'),
        'member' => __('messages.community.role_member'),
    ];
@endphp
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    @if($isMine ?? false)
        <a href="{{ route_path('teams.create') }}" class="btn"><i class="fa fa-plus"></i> {{ __('messages.account.create_team') }}</a>
    @endif
</div>

@if($ownedTeams->isNotEmpty())
<div style="margin-bottom:6px;font-size:13px;color:var(--meta-color);font-weight:600;text-transform:uppercase;letter-spacing:.5px">
    <i class="fa fa-crown" style="color:#f5a623"></i> {{ __('messages.community.my_teams') }}
</div>
<div class="user-team-list" style="margin-bottom:20px">
    @foreach($ownedTeams as $t)
    <div class="block user-team-card">
    <h2 class="user-tab-title" style="margin:0">{{ __('messages.account.nav_teams') }}</h2>
        <a href="{{ route_path('teams.show', $t->id) }}" class="user-team-card__photo">
            <img src="{{ $t->photo ?: asset('static/core/images/no_cover.webp') }}" alt="{{ $t->name }}">
        </a>
        <div class="user-team-card__info">
            <a href="{{ route_path('teams.show', $t->id) }}" class="user-team-card__name">{{ $t->name }}</a>
            <div style="font-size:12px;margin-top:3px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <span style="background:#f5a623;color:#000;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600">{{ __('messages.community.team_leader') }}</span>
                <span style="color:var(--meta-color)"><i class="fa fa-users"></i> {{ trans_choice('messages.community.members_count', $t->approved_members_count, ['count' => $t->approved_members_count]) }}</span>
                @if($t->site)<span style="color:var(--meta-color)"><i class="fa fa-link"></i> {{ $t->site }}</span>@endif
            </div>
        </div>
        @if($isMine ?? false)
        <div style="display:flex;gap:6px;flex-shrink:0">
            <a href="{{ route_path('teams.dashboard', $t->id) }}" class="btn btn-invincible" title="{{ __('messages.community.dashboard') }}"><i class="fa fa-tachometer-alt"></i></a>
            <a href="{{ route_path('teams.edit', $t->id) }}" class="btn btn-invincible" title="{{ __('messages.community.update_team') }}"><i class="fa fa-edit"></i></a>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

@if($memberTeams->isNotEmpty())
<div style="margin-bottom:6px;font-size:13px;color:var(--meta-color);font-weight:600;text-transform:uppercase;letter-spacing:.5px">
    <i class="fa fa-users"></i> {{ __('messages.community.joined_teams') }}
</div>
<div class="user-team-list">
    @foreach($memberTeams as $t)
    @php $myMembership = $t->members->first(); @endphp
    <div class="block user-team-card">
        <a href="{{ route_path('teams.show', $t->id) }}" class="user-team-card__photo">
            <img src="{{ $t->photo ?: asset('static/core/images/no_cover.webp') }}" alt="{{ $t->name }}">
        </a>
        <div class="user-team-card__info">
            <a href="{{ route_path('teams.show', $t->id) }}" class="user-team-card__name">{{ $t->name }}</a>
            <div style="font-size:12px;margin-top:3px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                @if($myMembership)
                @php $roleColors = ['admin'=>'#6c5ce7','editor'=>'#00b894','member'=>'#636e72']; $rc = $roleColors[$myMembership->role] ?? '#636e72'; @endphp
                <span style="background:{{ $rc }};color:#fff;border-radius:3px;padding:1px 6px;font-size:11px;font-weight:600">
                    {{ $teamRoleLabels[$myMembership->role] ?? $myMembership->role }}
                </span>
                @endif
                <span style="color:var(--meta-color)"><i class="fa fa-users"></i> {{ trans_choice('messages.community.members_count', $t->approved_members_count, ['count' => $t->approved_members_count]) }}</span>
                @if($t->site)<span style="color:var(--meta-color)"><i class="fa fa-link"></i> {{ $t->site }}</span>@endif
            </div>
        </div>
        <a href="{{ route_path('teams.show', $t->id) }}" class="btn btn-invincible" style="flex-shrink:0"><i class="fa fa-eye"></i></a>
    </div>
    @endforeach
</div>
@endif

@if($ownedTeams->isEmpty() && $memberTeams->isEmpty())
<div class="block"><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
    <i class="fa fa-user-friends" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
    {{ __('messages.account.teams_empty') }}
</div></div>
@endif

<style>
.user-team-list { display:flex; flex-direction:column; gap:10px; }
.user-team-card { display:flex; align-items:center; gap:14px; margin-bottom:0; }
.user-team-card__photo { width:54px; height:54px; border-radius:8px; overflow:hidden; flex-shrink:0; display:block; }
.user-team-card__photo img { width:100%; height:100%; object-fit:cover; }
.user-team-card__info { flex:1; min-width:0; }
.user-team-card__name { font-weight:600; text-decoration:none; color:inherit; }
.user-team-card__name:hover { color:var(--primary,#6c5ce7); }
</style>
@endsection
