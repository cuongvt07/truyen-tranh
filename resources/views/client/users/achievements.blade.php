@extends('client.users.profile')
@section('template_title', __('messages.account.nav_achievements'))

@push('styles')
<style>
/* ========== ACHIEVEMENTS ========== */
.ach-summary{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:24px}
.ach-stat{flex:1;min-width:110px;background:var(--bg-card,#fff);border:1px solid var(--border,#e5e7eb);border-radius:12px;padding:14px 12px;text-align:center}
.ach-stat .val{font-size:24px;font-weight:700;color:var(--primary,#6c63ff)}
.ach-stat .lbl{font-size:11px;color:var(--meta-color,#888);margin-top:3px}

.ach-section-title{font-size:14px;font-weight:700;margin:22px 0 10px;display:flex;align-items:center;gap:10px;color:var(--text-color,#222)}
.ach-section-title::after{content:'';flex:1;height:1px;background:var(--border,#e5e7eb)}

/* Badge grid */
.ach-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px}
.ach-badge{display:flex;flex-direction:column;align-items:center;gap:7px;padding:14px 10px;background:var(--bg-card,#fff);border:1px solid var(--border,#e5e7eb);border-radius:12px;text-align:center}
.ach-badge.earned{border-color:#6c63ff55;box-shadow:0 0 0 1px #6c63ff33 inset,0 2px 10px rgba(108,99,255,.1)}
.ach-badge .ach-icon{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--bg,#f3f4f6)}
.ach-badge.earned .ach-icon{background:linear-gradient(135deg,#ede9fe,#ddd6fe)}
.ach-badge .ach-icon img{width:26px;height:26px;opacity:.25;filter:grayscale(1)}
.ach-badge.earned .ach-icon img{opacity:1;filter:invert(37%) sepia(77%) saturate(700%) hue-rotate(220deg) brightness(85%) contrast(95%)}
.ach-badge .ach-name{font-size:11.5px;font-weight:600;line-height:1.3;color:var(--meta-color,#aaa)}
.ach-badge.earned .ach-name{color:var(--text-color,#111)}
.ach-badge .ach-reward{font-size:11px;font-weight:700;color:#6c63ff;background:#ede9fe;padding:2px 8px;border-radius:20px}
.ach-badge.locked .ach-reward{color:var(--meta-color,#bbb);background:var(--bg,#f3f4f6)}
.ach-badge .ach-date{font-size:10px;color:var(--meta-color,#bbb)}

/* Mission list */
.mission-list{display:flex;flex-direction:column;gap:10px}
.mission-item{background:var(--bg-card,#fff);border:1px solid var(--border,#e5e7eb);border-radius:12px;padding:13px 15px;display:flex;align-items:center;gap:13px}
.mission-item .m-icon{flex-shrink:0;width:42px;height:42px;border-radius:50%;background:var(--bg,#f3f4f6);display:flex;align-items:center;justify-content:center}
.mission-item .m-icon img{width:20px;height:20px;opacity:.28;filter:grayscale(1)}
.mission-item .m-body{flex:1;min-width:0}
.mission-item .m-name{font-size:13px;font-weight:600;color:var(--text-color,#111)}
.mission-item .m-desc{font-size:11.5px;color:var(--meta-color,#888);margin-top:2px}
.progress-track{height:5px;background:var(--bg,#f0f0f0);border-radius:99px;overflow:hidden;margin-top:7px}
.progress-fill{height:100%;background:linear-gradient(90deg,#6c63ff,#a78bfa);border-radius:99px}
.progress-label{font-size:10px;color:var(--meta-color,#aaa);margin-top:3px}
.mission-item .m-reward{white-space:nowrap;font-size:12px;font-weight:700;color:#6c63ff;background:#ede9fe;padding:3px 9px;border-radius:20px}

.cat-label{font-size:12px;font-weight:600;color:var(--meta-color,#888);margin:14px 0 6px}

@media(max-width:560px){
    .ach-grid{grid-template-columns:repeat(3,1fr);gap:8px}
    .ach-badge{padding:10px 6px}
    .ach-badge .ach-icon{width:42px;height:42px}
    .ach-summary{gap:8px}
}
</style>
@endpush

@section('user_content')
@php
    $iconUrl      = asset('static/core/images/award-solid.svg');
    $totalAll     = $all->count();
    $totalEarned  = $earned->count();
    $totalCredits = $earned->sum(fn($r) => $r['achievement']->reward_credits);
    $catLabels    = ['reading'=>'📖 Đọc truyện','social'=>'💬 Cộng đồng','support'=>'💎 Ủng hộ'];
@endphp

<h2 class="user-tab-title">{{ __('messages.account.nav_achievements') }}</h2>

{{-- Stats ngang --}}
<div class="ach-summary">
    <div class="ach-stat">
        <div class="val">{{ $totalEarned }}<span style="font-size:14px;color:var(--meta-color)">/{{ $totalAll }}</span></div>
        <div class="lbl">Thành tích</div>
    </div>
    <div class="ach-stat">
        <div class="val">{{ $totalCredits }}</div>
        <div class="lbl">Xu đã nhận</div>
    </div>
    <div class="ach-stat">
        <div class="val">{{ number_format($metrics['chapters_read']) }}</div>
        <div class="lbl">Chương đã đọc</div>
    </div>
    <div class="ach-stat">
        <div class="val">{{ number_format($metrics['comments_posted']) }}</div>
        <div class="lbl">Bình luận</div>
    </div>
</div>

{{-- ===== ĐÃ ĐẠT ===== --}}
<div class="ach-section-title"><span>🏆 Thành tích đã đạt</span></div>

@if($earned->isNotEmpty())
<div class="ach-grid">
    @foreach($earned as $row)
    @php $ach = $row['achievement']; @endphp
    <div class="ach-badge earned" title="{{ $ach->display_description }}">
        <div class="ach-icon"><img src="{{ $iconUrl }}" alt=""></div>
        <div class="ach-name">{{ $ach->display_name }}</div>
        <div class="ach-reward">+{{ $ach->reward_credits }} xu</div>
        @if($row['unlocked_at'])
        <div class="ach-date">{{ \Carbon\Carbon::parse($row['unlocked_at'])->format('d/m/Y') }}</div>
        @endif
    </div>
    @endforeach
</div>
@else
<div style="padding:24px;text-align:center;color:var(--meta-color,#aaa);font-size:13px">
    <img src="{{ $iconUrl }}" alt="" style="width:34px;opacity:.15;display:block;margin:0 auto 10px">
    Chưa có thành tích nào — hoàn thành nhiệm vụ bên dưới để nhận xu!
</div>
@endif

{{-- ===== NHIỆM VỤ ===== --}}
<div class="ach-section-title" style="margin-top:30px"><span>🎯 Nhiệm vụ</span></div>

@if($missions->isNotEmpty())
<div class="mission-list">
    @foreach($missions->groupBy(fn($r) => $r['achievement']->category) as $cat => $rows)
    <div class="cat-label">{{ $catLabels[$cat] ?? $cat }}</div>
    @foreach($rows as $row)
    @php $ach = $row['achievement']; $pct = $row['progress']; @endphp
    <div class="mission-item">
        <div class="m-icon"><img src="{{ $iconUrl }}" alt=""></div>
        <div class="m-body">
            <div class="m-name">{{ $ach->display_name }}</div>
            <div class="m-desc">{{ $ach->display_description }}</div>
            <div class="progress-track">
                <div class="progress-fill" style="width:{{ $pct }}%"></div>
            </div>
            <div class="progress-label">{{ number_format($row['current']) }} / {{ number_format($ach->target) }}</div>
        </div>
        <div class="m-reward">+{{ $ach->reward_credits }} xu</div>
    </div>
    @endforeach
    @endforeach
</div>
@else
<div style="padding:20px;text-align:center;color:var(--meta-color,#aaa);font-size:13px">
    Bạn đã hoàn thành tất cả nhiệm vụ! 🎉
</div>
@endif

@endsection
