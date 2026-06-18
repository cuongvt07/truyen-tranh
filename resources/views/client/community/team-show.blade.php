@extends('layout.novelight')
@section('template_title', $team->name . ' - Team')

@push('styles')
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}">
@endpush

@section('content')
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
                    <a href="{{ route('teams.dashboard', $team->id) }}">Dashboard</a>
                @elseif($isMember)
                    <span class="btn-invincible">Joined</span>
                @elseif($team->isPending())
                    <span class="btn-invincible">Under review</span>
                @else
                    <form method="POST" action="{{ route('teams.join', $team->id) }}">
                        @csrf
                        <button type="submit" class="primary-action">
                            <i class="fa fa-heart"></i> Subscribe
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="primary-action">
                    <i class="fa fa-heart"></i> Subscribe
                </a>
            @endauth

            @if($team->site)
                <a href="{{ $team->site }}" target="_blank" rel="nofollow">
                    <i class="fa fa-globe"></i> Website
                </a>
            @endif
            @if($team->donation_url)
                <a href="{{ $team->donation_url }}" target="_blank" rel="nofollow">
                    <i class="fa fa-gift"></i> {{ $team->donation_text ?: 'Donation' }}
                </a>
            @endif
        </aside>

        <main>
            <div class="team-kicker">Team</div>
            <h1 class="team-heading">{{ $team->name }}</h1>

            <div class="team-stats-line">
                <span class="team-stat-pill"><strong>0</strong><span>Likes</span></span>
                <span class="team-stat-pill"><strong>0</strong><span>Followers</span></span>
                <span class="team-stat-pill"><strong>{{ number_format($totalChapters) }}</strong><span>Chapters</span></span>
                <span class="team-stat-pill"><strong>{{ $articles->total() }}</strong><span>Books</span></span>
            </div>

            @if($team->isPending())
                <div class="review-alert">
                    <i class="fa fa-info-circle"></i>
                    The team is under review by administrators
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

            <h2 class="team-section-title">Members</h2>
            <div class="team-members-simple">
                @foreach($team->approvedMembers->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                    @php $profileUrl = optional($m->user)->id ? route('users.show.profile', $m->user) : '#'; @endphp
                    <a href="{{ $profileUrl }}" class="team-member">
                        <span class="team-member-avatar">
                            <img src="{{ optional($m->user)->avatar ?: asset('static/core/images/no_cover.webp') }}" alt="">
                        </span>
                        <span>
                            <span class="team-member-name">{{ optional($m->user)->username ?? '?' }}</span>
                            <span class="team-member-role">{{ $m->role }}</span>
                        </span>
                    </a>
                @endforeach
            </div>

            @if($articles->count())
                <h2 class="team-section-title" style="margin-top:18px">Books</h2>
                <div class="team-members-simple">
                    @foreach($articles as $art)
                        <a href="{{ route('articles.show', $art) }}" class="team-member">
                            <span class="team-member-avatar">
                                <img src="{{ $art->cover_image ?: asset('static/core/images/no_cover.webp') }}" alt="">
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
