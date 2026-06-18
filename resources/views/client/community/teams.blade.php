@extends('layout.novelight')
@section('template_title', __('messages.community.my_teams'))

@push('styles')
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}">
@endpush

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
            <h1><i class="fas fa-crown"></i> {{ __('messages.community.my_teams') }}</h1>
            <a href="{{ route('teams.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('messages.community.create_team_btn') }}
            </a>
        </div>
    </header>

    @if(session('success'))<div style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ session('success') }}</div>@endif
    @if(session('error'))<div style="background:#3a1010;border:1px solid #7a2020;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#f88">{{ session('error') }}</div>@endif

    <div class="block" style="padding:16px">
        @forelse($items as $t)
            <div class="my-story">
                <div class="my-story__poster">
                    <img src="{{ $t->photo ?: asset('static/core/images/no_cover.webp') }}" alt="">
                </div>
                <div class="my-story__info">
                    <div class="my-story__title">
                        <a href="{{ route('teams.show', $t->id) }}" style="color:inherit">{{ $t->name }}</a>
                        <span class="team-role-badge">{{ __('messages.community.team_leader') }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--meta-color);margin-top:3px">
                        <i class="fas fa-users"></i> {{ trans_choice('messages.community.members_count', $t->approved_members_count, ['count' => $t->approved_members_count]) }}
                        @if($t->site)
                            &nbsp;·&nbsp; <i class="fas fa-link"></i> {{ \Str::limit($t->site, 30) }}
                        @endif
                    </div>
                </div>
                <div class="my-story__actions">
                    <a href="{{ route('teams.edit', $t->id) }}" class="btn btn-invincible team-action-btn" title="{{ __('messages.community.update_team') }}" aria-label="{{ __('messages.community.update_team') }}">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                    </a>
                    <form method="post" action="{{ route('teams.destroy', $t->id) }}" onsubmit="return confirm('{{ __('messages.community.delete_team_confirm') }}')" style="display:inline">
                        @csrf
                        @method('delete')
                        <button class="btn btn-invincible team-action-btn team-action-btn--danger" title="{{ __('messages.community.delete_team') }}" aria-label="{{ __('messages.community.delete_team') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
                <i class="fas fa-users" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
                {{ __('messages.community.no_teams') }} <a href="{{ route('teams.create') }}">{{ __('messages.community.create_now') }}</a>
            </div>
        @endforelse
        <div style="margin-top:16px">{{ $items->links('vendor.pagination.novelight') }}</div>
    </div>
</div>
<style>
.my-story { display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--border,#2a2a3e); }
.my-story__poster { width:54px; height:54px; flex-shrink:0; border-radius:8px; overflow:hidden; }
.my-story__poster img { width:100%; height:100%; object-fit:cover; }
.my-story__info { flex:1; min-width:0; }
.my-story__title { display:flex; align-items:center; gap:8px; flex-wrap:wrap; font-weight:600; }
.team-role-badge { display:inline-flex; align-items:center; padding:3px 6px; border-radius:3px; background:#ffa500; color:#000; font-size:12px; font-weight:700; line-height:1; }
.my-story__actions { display:flex; gap:6px; }
.my-story__actions .team-action-btn {
    width:34px;
    height:34px;
    padding:0;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    color:#333 !important;
    font-size:14px;
    line-height:1;
}
.my-story__actions .team-action-btn i { display:block; color:inherit !important; }
.my-story__actions .team-action-btn--danger { color:#e84040 !important; }
@media(max-width:560px) {
    .my-story { align-items:flex-start; }
    .my-story__actions { flex-direction:column; }
}
</style>
@endsection
