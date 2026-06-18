@extends('layout.novelight')
@section('template_title', $team->name . ' — Nhóm dịch')

@push('styles')
<link rel="stylesheet" href="{{ asset('static/character/css/character.css') }}">
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="block character-block">
        {{-- Sidebar --}}
        <div class="poster">
            <div class="image image-cover">
                <img src="{{ $team->photo ?: asset('static/core/images/no_cover.webp') }}" alt="{{ $team->name }}">
            </div>
            <div class="character-mobile-header">
                <div class="character-tag">Nhóm dịch</div>
                <div class="h1">{{ $team->name }}</div>
            </div>

            @auth
                @if($isLeader)
                    <a href="{{ route('teams.dashboard', $team->id) }}" class="btn">
                        <i class="fa fa-tachometer-alt"></i> Dashboard
                    </a>
                @elseif($isMember)
                    <span class="btn btn-invincible" style="cursor:default;opacity:.7">
                        <i class="fa fa-check"></i> Đã tham gia
                    </span>
                @else
                    <form method="POST" action="{{ route('teams.join', $team->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-invincible">
                            <i class="fa fa-user-plus"></i> Xin gia nhập
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-invincible">
                    <i class="fa fa-user-plus"></i> Xin gia nhập
                </a>
            @endauth

            <hr>

            <div class="team-sites">
                @if($team->donation_url)
                    <a href="{{ $team->donation_url }}" target="_blank" rel="nofollow" class="btn btn-invincible clamp clamp-1">
                        <i class="fa fa-gift"></i> {{ $team->donation_text ?: 'Ủng hộ nhóm' }}
                    </a>
                @endif
                @if($team->site)
                    <a href="{{ $team->site }}" target="_blank" rel="nofollow" class="btn btn-invincible clamp clamp-1">
                        <i class="fa fa-globe"></i> Website
                    </a>
                @endif
            </div>
        </div>

        {{-- Main content --}}
        <div class="character-info">
            <span class="character-tag">Nhóm dịch</span>
            <h1>{{ $team->name }}</h1>

            {{-- Stats --}}
            <section class="section stats">
                <div class="stat">
                    <div class="num">{{ $team->approvedMembers->count() }}</div>
                    <div class="text">Thành viên</div>
                </div>
                <div class="stat">
                    <div class="num">{{ $articles->total() }}</div>
                    <div class="text">Truyện</div>
                </div>
                <div class="stat">
                    <div class="num">{{ number_format($totalChapters) }}</div>
                    <div class="text">Chương</div>
                </div>
            </section>

            {{-- Description --}}
            @if($team->description)
            <section class="text-info section">
                {!! nl2br(e($team->description)) !!}
            </section>
            @endif

            {{-- Recent books — grid 2 col: left = book thumb+title, right = chapter name --}}
            @if($articles->count())
            <section class="section last-chapters">
                <h2>Truyện mới cập nhật</h2>
                <div class="last-chapters__list">
                    @foreach($articles->take(4) as $art)
                        <a href="{{ route('articles.show', $art) }}" class="item">
                            <div class="image image-cover">
                                <img src="{{ $art->cover_image ?: asset('static/core/images/no_cover.webp') }}"
                                     alt="{{ $art->title }}" loading="lazy">
                            </div>
                            <div class="title clamp clamp-2">{{ $art->title }}</div>
                        </a>
                        @if($art->chapters->isNotEmpty())
                            <a href="{{ route('articles.chapters.show', [$art, $art->chapters->first()->number]) }}"
                               class="chapter-name">
                                Chương {{ $art->chapters->first()->number }}
                            </a>
                        @else
                            <span class="chapter-name" style="color:var(--meta-color)">—</span>
                        @endif
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Members — 4-col grid --}}
            @if($team->approvedMembers->count())
            <section class="section members">
                <h2>Thành viên</h2>
                <div class="members-list">
                    @foreach($team->approvedMembers->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                    <a href="{{ route('users.profile', optional($m->user)->id ?? 0) }}" class="member">
                        <div class="profile-avatar image image-cover">
                            <img src="{{ optional($m->user)->photo ?: asset('static/core/images/no_cover.webp') }}"
                                 alt="" loading="lazy">
                        </div>
                        <div class="member-info">
                            <div class="nickname clamp clamp-1">{{ optional($m->user)->username ?? '?' }}</div>
                            <div class="role">{{ \App\Models\TeamMember::ROLES[$m->role] ?? $m->role }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </div>

    {{-- Book grid --}}
    @if($articles->count())
    <div class="block">
        <div class="manga-grid-list">
            @foreach($articles as $art)
            <a href="{{ route('articles.show', $art) }}" class="item">
                <div class="poster image image-cover">
                    <img src="{{ $art->cover_image ?: asset('static/core/images/no_cover.webp') }}"
                         alt="{{ $art->title }}" loading="lazy">
                </div>
                <div class="title clamp clamp-2">{{ $art->title }}</div>
            </a>
            @endforeach
        </div>
        <div style="margin-top:16px">{{ $articles->links('vendor.pagination.novelight') }}</div>
    </div>
    @endif
</div>

@if(session('success'))
<div id="join-toast" style="position:fixed;bottom:20px;right:20px;background:#1a3a1a;border:1px solid #2e5e2e;color:#9f9;padding:12px 18px;border-radius:8px;z-index:9999;max-width:320px">
    {{ session('success') }}
</div>
<script>setTimeout(function(){ var t=document.getElementById('join-toast'); if(t) t.remove(); }, 4000);</script>
@endif

@if(session('error'))
<div id="err-toast" style="position:fixed;bottom:20px;right:20px;background:#3a1010;border:1px solid #7a2020;color:#f88;padding:12px 18px;border-radius:8px;z-index:9999;max-width:320px">
    {{ session('error') }}
</div>
<script>setTimeout(function(){ var t=document.getElementById('err-toast'); if(t) t.remove(); }, 5000);</script>
@endif
@endsection
