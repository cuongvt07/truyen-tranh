@extends('layout.novelight')
@section('template_title', $team->name . ' — Nhóm dịch')

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
                @elseif(!$isMember)
                    <form method="POST" action="{{ route('teams.join', $team->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-invincible">
                            <i class="fa fa-user-plus"></i> Xin gia nhập
                        </button>
                    </form>
                @else
                    <span class="btn btn-invincible" style="cursor:default;opacity:.7">
                        <i class="fa fa-check"></i> Đã tham gia
                    </span>
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

            {{-- Recent books --}}
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
                        @endif
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Members --}}
            @if($team->approvedMembers->count())
            <section class="section members">
                <h2>Thành viên</h2>
                <div class="members-list">
                    @foreach($team->approvedMembers->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                    <a href="{{ route('users.profile', optional($m->user)->id ?? '#') }}" class="member">
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
<script>setTimeout(()=>{ var t=document.getElementById('join-toast'); if(t) t.remove(); }, 4000);</script>
@endif

@if(session('error'))
<div id="err-toast" style="position:fixed;bottom:20px;right:20px;background:#3a1010;border:1px solid #7a2020;color:#f88;padding:12px 18px;border-radius:8px;z-index:9999;max-width:320px">
    {{ session('error') }}
</div>
<script>setTimeout(()=>{ var t=document.getElementById('err-toast'); if(t) t.remove(); }, 5000);</script>
@endif


<style>
.team-sites { display:flex; flex-direction:column; gap:8px; }
.members-list { display:flex; flex-wrap:wrap; gap:10px; }
.member { display:flex; align-items:center; gap:10px; text-decoration:none; color:inherit;
          background:var(--card-bg,#13131f); border:1px solid var(--border,#2a2a3e);
          border-radius:8px; padding:8px 12px; min-width:140px; }
.member:hover { border-color:var(--primary,#6c5ce7); }
.profile-avatar { width:36px; height:36px; border-radius:50%; overflow:hidden; flex-shrink:0; }
.profile-avatar img { width:100%; height:100%; object-fit:cover; }
.member-info .nickname { font-weight:600; font-size:13px; }
.member-info .role { font-size:11px; color:var(--meta-color); margin-top:2px; }
.last-chapters__list { display:grid; grid-template-columns:repeat(auto-fill,minmax(90px,1fr)); gap:10px; }
.last-chapters__list .item { text-decoration:none; color:inherit; }
.last-chapters__list .item .image { border-radius:6px; overflow:hidden; aspect-ratio:2/3; }
.last-chapters__list .item .title { font-size:12px; margin-top:4px; }
.last-chapters__list .chapter-name { font-size:11px; color:var(--meta-color); display:block; }
</style>
@endsection
