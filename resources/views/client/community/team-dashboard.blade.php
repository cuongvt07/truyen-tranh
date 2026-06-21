@extends('layout.novelight')
@section('template_title', __('messages.community.dashboard') . ' - ' . $team->name)

@push('styles')
@php $teamCssVer = file_exists(public_path('static/team/css/team.css')) ? filemtime(public_path('static/team/css/team.css')) : time(); @endphp
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}?v={{ $teamCssVer }}">
@endpush

@section('content')
<div class="team-page">
    <h1 class="team-title">{{ __('messages.community.dashboard') }}</h1>

    <div class="team-layout">
        <main class="team-main team-panel">
            @if($team->isPending())
                <div class="review-alert">
                    <i class="fa fa-info-circle"></i>
                    {{ __('messages.community.pending_review_notice') }}
                </div>
            @endif

            <div class="dashboard-grid">
                <section class="dashboard-chart">
                    <h2>{{ __('messages.community.coupons_per_days') }}</h2>
                    <div class="chart-legend">
                        <span class="legend-item coupons">{{ __('messages.community.coupons') }}</span>
                        <span class="legend-item likes">{{ __('messages.community.likes') }}</span>
                    </div>
                    <div class="chart-box">
                        <div class="chart-zero-line"></div>
                        <div class="team-chart-series">
                            @foreach($chartData as $day)
                                <div class="team-chart-day">
                                    <span class="team-chart-bar coupons" style="height:{{ max(2, round(($day['coupons'] / $chartMax) * 190)) }}px" title="{{ $day['label'] }}: {{ number_format($day['coupons']) }} {{ __('messages.community.coupons') }}"></span>
                                    <span class="team-chart-bar likes" style="height:{{ max(2, round(($day['likes'] / $chartMax) * 190)) }}px" title="{{ $day['label'] }}: {{ number_format($day['likes']) }} {{ __('messages.community.likes') }}"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="chart-axis">
                        @foreach($chartData as $day)
                            <span>{{ $day['label'] }}</span>
                        @endforeach
                    </div>
                </section>

                <aside class="metric-column">
                    <div class="metric-card">
                        <div class="label">{{ __('messages.community.monthly_likes') }}</div>
                        <div class="value">{{ number_format($monthlyLikes) }}</div>
                        <div class="compare">{{ $likesCompare }}</div>
                    </div>
                    <div class="metric-card">
                        <div class="label">{{ __('messages.community.monthly_coupons') }}</div>
                        <div class="value">{{ number_format($monthlyCoupons) }}</div>
                        <div class="compare">{{ $couponsCompare }}</div>
                    </div>
                </aside>
            </div>

            <div class="small-stat-grid">
                <div class="small-stat">
                    <div class="label">{{ __('messages.community.number_of_books') }}</div>
                    <div class="value">{{ $totalArticles }}</div>
                </div>
                <div class="small-stat">
                    <div class="label">{{ __('messages.community.number_of_chapters') }}</div>
                    <div class="value">{{ number_format($totalChapters) }}</div>
                </div>
                <div class="small-stat">
                    <div class="label">{{ __('messages.community.team_balance_coupons') }}</div>
                    <div class="value">{{ number_format($teamBalance) }}</div>
                </div>
            </div>

            <div class="dashboard-lists">
                <section class="dashboard-list">
                    <h2>{{ __('messages.community.top_likes_month') }}</h2>
                    @if($topLikedArticles->isEmpty())
                        <div class="empty-data">{{ __('messages.community.no_data') }}</div>
                    @else
                        <div class="dashboard-rank">
                            @foreach($topLikedArticles as $row)
                                <a href="{{ route('articles.show', $row->id) }}" class="dashboard-rank-row">
                                    <span class="rank-title">{{ $row->title }}</span>
                                    <span class="rank-value">{{ number_format($row->total) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
                <section class="dashboard-list">
                    <h2>{{ __('messages.community.top_coupons_month') }}</h2>
                    @if($topCouponArticles->isEmpty())
                        <div class="empty-data">{{ __('messages.community.no_data') }}</div>
                    @else
                        <div class="dashboard-rank">
                            @foreach($topCouponArticles as $row)
                                <a href="{{ route('articles.show', $row->id) }}" class="dashboard-rank-row">
                                    <span class="rank-title">{{ $row->title }}</span>
                                    <span class="rank-value">{{ number_format($row->total) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <div class="dashboard-note">{{ __('messages.community.dashboard_update_note') }}</div>
        </main>

        @include('client.community._team_sidebar')
    </div>
</div>
@endsection
