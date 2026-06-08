@extends('client.users.profile')
@section('template_title', __('messages.account.nav_teams'))

@section('user_content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
    <h2 style="margin:0">{{ __('messages.account.nav_teams') }}</h2>
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
    <div class="block">
        @foreach($teams as $t)
            <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border,#2a2a3e)">
                <div style="width:54px;height:54px;border-radius:8px;overflow:hidden;flex-shrink:0">
                    <img src="{{ $t->photo ?: asset('static/core/images/no_cover.webp') }}" style="width:100%;height:100%;object-fit:cover">
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600">{{ $t->name }}</div>
                    @if($t->site)<div class="meta-color" style="font-size:13px"><i class="fa fa-link"></i> {{ $t->site }}</div>@endif
                </div>
                @if($isMine ?? false)<a href="{{ route('teams.edit', $t->id) }}" class="btn btn-invincible"><i class="fa fa-edit"></i></a>@endif
            </div>
        @endforeach
    </div>
@endif
@endsection
