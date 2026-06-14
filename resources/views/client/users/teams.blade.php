@extends('client.users.profile')
@section('template_title', __('messages.account.nav_teams'))

@section('user_content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
    <h2 class="user-tab-title" style="margin:0">{{ __('messages.account.nav_teams') }}</h2>
    @if($isMine ?? false)
        <a href="{{ route('teams.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('messages.account.create_team') }}</a>
    @endif
</div>

@if($teams->isEmpty())
    <div class="block"><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
        <i class="fa fa-user-friends" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
        {{ __('messages.account.teams_empty') }}
    </div></div>
@else
    <div class="user-team-list">
        @foreach($teams as $t)
            <div class="block user-team-card">
                <div class="user-team-card__photo">
                    <img src="{{ $t->photo ?: asset('static/core/images/no_cover.webp') }}" alt="{{ $t->name }}">
                </div>
                <div class="user-team-card__info">
                    <div class="user-team-card__name">{{ $t->name }}</div>
                    @if($t->site)<div class="meta-color" style="font-size:13px"><i class="fa fa-link"></i> {{ $t->site }}</div>@endif
                </div>
                @if($isMine ?? false)<a href="{{ route('teams.edit', $t->id) }}" class="btn btn-invincible"><i class="fa fa-edit"></i></a>@endif
            </div>
        @endforeach
    </div>
    <style>
    /* Mỗi nhóm là 1 card riêng, có khoảng cách rõ ràng trên PC (trước đây dồn chung 1 khối, dính sát nhau) */
    .user-team-list { display:flex; flex-direction:column; gap:12px; }
    .user-team-card { display:flex; align-items:center; gap:14px; margin-bottom:0; }
    .user-team-card__photo { width:54px; height:54px; border-radius:8px; overflow:hidden; flex-shrink:0; }
    .user-team-card__photo img { width:100%; height:100%; object-fit:cover; }
    .user-team-card__info { flex:1; min-width:0; }
    .user-team-card__name { font-weight:600; }
    </style>
@endif
@endsection
