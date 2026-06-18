@extends('layout.novelight')
@section('template_title', 'Dashboard - ' . $team->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('static/team/css/team.css') }}">
@endpush

@section('content')
<div class="team-page">
    <h1 class="team-title">Dashboard</h1>

    <div class="team-layout">
        <main class="team-main team-panel">
            @if($team->isPending())
                <div class="review-alert">
                    <i class="fa fa-info-circle"></i>
                    The team is under review by administrators
                </div>
            @endif

            <div class="dashboard-grid">
                <section class="dashboard-chart">
                    <h2>Coupons per days</h2>
                    <div class="chart-legend">
                        <span class="legend-item coupons">Coupons</span>
                        <span class="legend-item likes">Likes</span>
                    </div>
                    <div class="chart-box">
                        <div class="chart-zero-line"></div>
                    </div>
                    <div class="chart-axis">
                        <span>10 May</span><span>21 May</span><span>25 May</span><span>27 May</span><span>29 May</span>
                        <span>31 May</span><span>02 Jun</span><span>04 Jun</span><span>08 Jun</span><span>10 Jun</span>
                        <span>12 Jun</span><span>14 Jun</span><span>16 Jun</span><span>18 Jun</span><span>19 Jun</span>
                    </div>
                </section>

                <aside class="metric-column">
                    <div class="metric-card">
                        <div class="label">Number of likes for the month</div>
                        <div class="value">0</div>
                        <div class="compare">No data to compare</div>
                    </div>
                    <div class="metric-card">
                        <div class="label">Number of coupons for the month</div>
                        <div class="value">0</div>
                        <div class="compare">No data to compare</div>
                    </div>
                </aside>
            </div>

            <div class="small-stat-grid">
                <div class="small-stat">
                    <div class="label">Number of books</div>
                    <div class="value">{{ $totalArticles }}</div>
                </div>
                <div class="small-stat">
                    <div class="label">Number of chapters</div>
                    <div class="value">{{ number_format($totalChapters) }}</div>
                </div>
                <div class="small-stat">
                    <div class="label">Team balance (coupons)</div>
                    <div class="value">0</div>
                </div>
            </div>

            <div class="dashboard-lists">
                <section class="dashboard-list">
                    <h2>The most likes in a month</h2>
                    <div class="empty-data">No data to display</div>
                </section>
                <section class="dashboard-list">
                    <h2>The most coupons in a month</h2>
                    <div class="empty-data">No data to display</div>
                </section>
            </div>

            <div class="dashboard-note">The data is updated every 6 hours.</div>
        </main>

        @include('client.community._team_sidebar')
    </div>
</div>
@endsection
