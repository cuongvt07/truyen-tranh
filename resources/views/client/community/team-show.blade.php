@extends('layout.novelight')
@section('template_title', $team->name . ' - ' . __('messages.community.team'))

@push('styles')
@php $teamCssVer = file_exists(public_path('static/team/css/team.css')) ? filemtime(public_path('static/team/css/team.css')) : time(); @endphp
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}?v={{ $teamCssVer }}">
@endpush

@section('content')
@php
    $teamRoleLabels = [
        'leader' => __('messages.community.role_leader'),
        'admin' => __('messages.community.role_admin'),
        'editor' => __('messages.community.role_editor'),
        'member' => __('messages.community.role_member'),
    ];
@endphp
<div class="team-page">
    <div class="team-panel team-public">
        <aside class="team-public-sidebar">
            <div class="team-photo-strip">
                <img src="{{ $team->photo ?: asset('static/core/images/no_cover.webp') }}" alt="">
                <img src="{{ $team->photo ?: asset('static/core/images/no_cover.webp') }}" alt="">
                <img src="{{ $team->photo ?: asset('static/core/images/no_cover.webp') }}" alt="">
            </div>

            @auth
                @if($isLeader)
                    <a href="{{ route('teams.dashboard', $team->id) }}">{{ __('messages.community.dashboard') }}</a>
                @elseif($isMember)
                    <span class="btn-invincible">{{ __('messages.community.joined') }}</span>
                @elseif($team->isPending())
                    <span class="btn-invincible">{{ __('messages.community.under_review') }}</span>
                @else
                    <form method="POST" action="{{ route('teams.join', $team->id) }}">
                        @csrf
                        <button type="submit" class="primary-action">
                            <i class="fa fa-heart"></i> {{ __('messages.community.subscribe') }}
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="primary-action">
                    <i class="fa fa-heart"></i> {{ __('messages.community.subscribe') }}
                </a>
            @endauth

            @if($team->site)
                <a href="{{ $team->site }}" target="_blank" rel="nofollow">
                    <i class="fa fa-globe"></i> {{ __('messages.community.website') }}
                </a>
            @endif
            @if($team->donation_url)
                <a href="{{ $team->donation_url }}" target="_blank" rel="nofollow">
                    <i class="fa fa-gift"></i> {{ $team->donation_text ?: __('messages.community.donation') }}
                </a>
            @endif
        </aside>

        <main>
            <div class="team-kicker">{{ __('messages.community.team') }}</div>
            <h1 class="team-heading">{{ $team->name }}</h1>

            <div class="team-stats-line">
                <span class="team-stat-pill"><strong>0</strong><span>{{ __('messages.community.likes') }}</span></span>
                <span class="team-stat-pill"><strong>0</strong><span>{{ __('messages.community.followers') }}</span></span>
                <span class="team-stat-pill"><strong>{{ number_format($totalChapters) }}</strong><span>{{ __('messages.community.chapters') }}</span></span>
                <span class="team-stat-pill"><strong>{{ $articles->total() }}</strong><span>{{ __('messages.community.books') }}</span></span>
            </div>

            @if($team->isPending())
                <div class="review-alert">
                    <i class="fa fa-info-circle"></i>
                    {{ __('messages.community.pending_review_notice') }}
                </div>
            @endif

            @if(session('success'))
                <div class="review-alert" style="background:#317a31">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="review-alert" style="background:#a52a2a">{{ session('error') }}</div>
            @endif

            @if($team->description)
                <div class="team-description">{!! nl2br(e($team->description)) !!}</div>
            @endif

            <h2 class="team-section-title">{{ __('messages.community.members') }}</h2>
            <div class="team-members-simple">
                @foreach($team->approvedMembers->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                    @php $profileUrl = optional($m->user)->id ? route('users.show.profile', $m->user) : '#'; @endphp
                    <a href="{{ $profileUrl }}" class="team-member">
                        <span class="team-member-avatar">
                            <img src="{{ optional($m->user)->avatar ?: asset('static/core/images/no_cover.webp') }}" alt="">
                        </span>
                        <span>
                            <span class="team-member-name">{{ optional($m->user)->username ?? '?' }}</span>
                            <span class="team-member-role">{{ $teamRoleLabels[$m->role] ?? $m->role }}</span>
                        </span>
                    </a>
                @endforeach
            </div>

            @if($articles->count())
                <h2 class="team-section-title" style="margin-top:18px">{{ __('messages.community.books') }}</h2>
                <div class="team-members-simple">
                    @foreach($articles as $art)
                        <a href="{{ route('articles.show', $art) }}" class="team-member">
                            <span class="team-member-avatar">
                                <img src="{{ novel_poster($art) }}" alt="{{ $art->title }}">
                            </span>
                            <span class="team-member-name">{{ $art->title }}</span>
                        </a>
                    @endforeach
                </div>
                <div style="margin-top:14px">{{ $articles->links('vendor.pagination.novelight') }}</div>
            @endif
        </main>
    </div>
</div>
@endsection
