@extends('layout.novelight')
@section('template_title', 'Dashboard — ' . $team->name)

@section('content')
<div class="container">
    <h1 class="page-title">Dashboard</h1>

    <div class="flex-content">
        <div class="main block">
            {{-- Stats --}}
            <div class="section dashboard-stats">
                <div class="info-stat">
                    <div class="name">Số truyện</div>
                    <div class="num">{{ $totalArticles }}</div>
                </div>
                <div class="info-stat">
                    <div class="name">Số chương</div>
                    <div class="num">{{ number_format($totalChapters) }}</div>
                </div>
                <div class="info-stat">
                    <div class="name">Thành viên</div>
                    <div class="num">{{ $team->approvedMembers->count() }}</div>
                </div>
            </div>

            {{-- Members quick list --}}
            <div class="section" style="margin-top:24px">
                <h2 class="section-title">Thành viên nhóm</h2>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px">
                    @foreach($team->approvedMembers->sortBy(fn($m) => array_search($m->role, ['leader','admin','editor','member'])) as $m)
                    <div style="display:flex;align-items:center;gap:8px;background:var(--input-bg,#1a1a2e);border:1px solid var(--border,#2a2a3e);border-radius:8px;padding:8px 12px">
                        <img src="{{ optional($m->user)->photo ?: asset('static/core/images/no_cover.webp') }}"
                             style="width:28px;height:28px;border-radius:50%;object-fit:cover">
                        <div>
                            <div style="font-size:13px;font-weight:600">{{ optional($m->user)->username ?? '?' }}</div>
                            <div style="font-size:11px;color:var(--meta-color)">{{ \App\Models\TeamMember::ROLES[$m->role] ?? $m->role }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="meta-color" style="text-align:center;margin-top:24px;font-size:13px">
                Thống kê chi tiết sẽ được bổ sung sau.
            </div>
        </div>

        @include('client.community._team_sidebar')
    </div>
</div>

<style>
.flex-content { display:flex; gap:20px; align-items:flex-start; }
.flex-content .main { flex:1; min-width:0; }
.second-information { width:200px; flex-shrink:0; }
.btn-list { display:flex; flex-direction:column; gap:6px; padding:16px; }
.btn-list .btn, .btn-list .btn-invincible { display:block; text-align:center; }
.page-title { font-size:22px; font-weight:700; margin-bottom:20px; }
.section-title { font-size:15px; font-weight:700; margin-bottom:8px; }
.dashboard-stats { display:flex; flex-wrap:wrap; gap:16px; }
.info-stat { background:var(--card-bg,#13131f); border:1px solid var(--border,#2a2a3e); border-radius:10px; padding:16px 20px; min-width:140px; flex:1; }
.info-stat .name { font-size:12px; color:var(--meta-color); margin-bottom:6px; }
.info-stat .num { font-size:28px; font-weight:700; }
@media(max-width:640px) { .flex-content { flex-direction:column; } .second-information { width:100%; } }
</style>
@endsection
