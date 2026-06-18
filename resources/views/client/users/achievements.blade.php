@extends('client.users.profile')
@section('template_title', __('messages.account.nav_achievements'))

@push('styles')
<style>
.achievement,
.notification {
    position: relative;
    display: flex;
    color: var(--text-color);
    text-decoration: none;
    padding: 10px;
    border-radius: 4px;
    background: var(--light-gray-color);
    margin-bottom: 10px;
}
.achievement .image,
.notification .image {
    width: 74px;
    margin-right: 15px;
    flex-shrink: 0;
}
.achievement .title,
.notification .title {
    font-size: 20px;
    font-weight: 500;
}
.achievement {
    justify-content: space-between;
    align-items: center;
}
.achievement-info-wrapper {
    display: flex;
}
.achievement .image {
    height: 74px;
}
.achievement-list {
    display: flex;
    flex-direction: column;
}
.achievement-item.status-waiting {
    order: 0;
}
.achievement-item.status-completed {
    order: 1;
}
.achievement-item.status-None {
    opacity: 0.4;
    order: 2;
}
.achievement-info .decpr {
    font-size: 14px;
    color: var(--meta-color, #888);
    margin-top: 4px;
}
.achievement-info .ach-progress {
    margin-top: 8px;
}
.ach-progress-track {
    height: 4px;
    background: var(--border-color, #e0e0e0);
    border-radius: 99px;
    overflow: hidden;
    width: 180px;
    max-width: 100%;
}
.ach-progress-fill {
    height: 100%;
    background: var(--text-color, #222);
    border-radius: 99px;
}
.ach-progress-label {
    font-size: 11px;
    color: var(--meta-color, #999);
    margin-top: 3px;
}
.btn-reward {
    flex-shrink: 0;
    white-space: nowrap;
    font-size: 14px;
    font-weight: 600;
    padding: 0 8px;
    text-align: right;
}
.btn-reward .ach-date {
    display: block;
    font-size: 11px;
    font-weight: 400;
    color: var(--meta-color, #999);
    margin-top: 4px;
}
.ach-summary-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.ach-stat-chip {
    background: var(--light-gray-color);
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 13px;
}
.ach-stat-chip strong {
    font-size: 20px;
    display: block;
}
</style>
@endpush

@section('user_content')
@php
    $iconUrl      = asset('static/core/images/award-solid.svg');
    $coinLabel    = coin_name();
    $totalAll     = $all->count();
    $totalEarned  = $earned->count();
    $totalCredits = $earned->sum(fn($r) => $r['achievement']->reward_credits);
@endphp

<h2 class="user-tab-title">{{ __('messages.account.nav_achievements') }}</h2>

{{-- Stats --}}
<div class="ach-summary-row">
    <div class="ach-stat-chip">
        <strong>{{ $totalEarned }}<span style="font-size:13px;font-weight:400"> / {{ $totalAll }}</span></strong>
        {{ __('messages.account.ach_stat_total') }}
    </div>
    <div class="ach-stat-chip">
        <strong>{{ $totalCredits }}</strong>
        {{ __('messages.account.ach_stat_coins_received', ['coin' => $coinLabel]) }}
    </div>
    <div class="ach-stat-chip">
        <strong>{{ number_format($metrics['chapters_read']) }}</strong>
        {{ __('messages.account.ach_stat_chapters') }}
    </div>
    <div class="ach-stat-chip">
        <strong>{{ number_format($metrics['comments_posted']) }}</strong>
        {{ __('messages.account.ach_stat_comments') }}
    </div>
</div>

{{-- Achievement list --}}
<div class="achievement-list">
@forelse($all as $row)
@php
    $ach = $row['achievement'];
    if ($row['earned']) {
        $status = 'status-completed';
    } elseif ($row['current'] > 0) {
        $status = 'status-waiting';
    } else {
        $status = 'status-None';
    }
@endphp
<div class="achievement-item {{ $status }}">
    <div class="achievement">
        <div class="achievement-info-wrapper">
            <div class="image">
                <img class="lazy-image loaded" loading="lazy" src="{{ $iconUrl }}" alt="">
            </div>
            <div class="achievement-info">
                <div class="title clamp clamp-3">{{ $ach->display_name }}</div>
                <div class="decpr">{{ $ach->display_description }}</div>
                @if(!$row['earned'] && $row['current'] > 0)
                <div class="ach-progress">
                    <div class="ach-progress-track">
                        <div class="ach-progress-fill" style="width:{{ $row['progress'] }}%"></div>
                    </div>
                    <div class="ach-progress-label">
                        {{ __('messages.account.ach_progress', ['current' => number_format($row['current']), 'target' => number_format($ach->target)]) }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="btn-reward">
            <span>{{ $ach->reward_credits }} {{ $coinLabel }}</span>
            @if($row['earned'] && $row['unlocked_at'])
            <span class="ach-date">{{ \Carbon\Carbon::parse($row['unlocked_at'])->format('d/m/Y') }}</span>
            @endif
        </div>
    </div>
</div>
@empty
<div style="padding:40px;text-align:center;color:var(--meta-color,#aaa)">
    <img src="{{ $iconUrl }}" alt="" style="width:40px;opacity:.15;display:block;margin:0 auto 12px">
    {{ __('messages.account.ach_empty_hint', ['coin' => $coinLabel]) }}
</div>
@endforelse
</div>

@endsection
