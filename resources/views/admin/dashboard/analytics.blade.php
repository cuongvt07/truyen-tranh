@extends('layout.admin')

@section('template_title', 'Analytics')

@push('styles')
<style>
    .analytics-story-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.35;
        max-height: 2.7em;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">Bảng Analytics QTV</h5>
            <div class="text-muted small">Dữ liệu tháng hiện tại: {{ $analyticsPeriodLabel }}</div>
        </div>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-receipt"></i> Giao dịch
        </a>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($monthlyBuyers) }}</h3>
                    <p>Người mua tháng này</p>
                    <small>{{ number_format($monthlyTransactions) }} giao dịch hoàn tất</small>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <a href="{{ route('admin.transactions.index', ['status' => 'completed']) }}" class="small-box-footer">
                    Xem giao dịch <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($monthlyRevenue) }}</h3>
                    <p>Doanh thu credit</p>
                    <small>Đơn vị theo amount giao dịch</small>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
                <a href="{{ route('admin.credit-packages.index') }}" class="small-box-footer">
                    Gói credit <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($monthlyCreditReaders) }}</h3>
                    <p>Người đọc trả credit</p>
                    <small>{{ number_format($monthlyCreditUnlocks) }} lượt mở khóa</small>
                </div>
                <div class="icon"><i class="fas fa-unlock"></i></div>
                <span class="small-box-footer">Đã tiêu {{ number_format($monthlyCreditsSpent) }} credit</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($monthlyReadingUsers) }}</h3>
                    <p>Người đọc tháng này</p>
                    <small>{{ number_format($monthlyReads) }} lượt đọc ghi nhận</small>
                </div>
                <div class="icon"><i class="fas fa-book-reader"></i></div>
                <a href="{{ route('admin.articles.index') }}" class="small-box-footer">
                    Xem truyện <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Xu hướng mua credit 30 ngày</h3>
                </div>
                <div class="card-body">
                    <canvas id="purchaseTrendChart" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bell text-danger mr-1"></i> Cần xử lý</h3>
                </div>
                <div class="card-body p-0">
                    <a href="{{ route('admin.articles.index', ['status' => 0]) }}" class="info-box mb-0">
                        <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Truyện chờ duyệt</span>
                            <span class="info-box-number">{{ number_format($pendingArticles) }}</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.comment_reports.index') }}" class="info-box mb-0">
                        <span class="info-box-icon bg-danger"><i class="fas fa-flag"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Báo cáo bình luận</span>
                            <span class="info-box-number">{{ number_format($openReports) }}</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.articles.index', ['status' => 2]) }}" class="info-box mb-0">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-eye-slash"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Truyện đang ẩn</span>
                            <span class="info-box-number">{{ number_format($hiddenArticles) }}</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Đọc trả credit 30 ngày</h3>
                </div>
                <div class="card-body">
                    <canvas id="creditTrendChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Lượt đọc 30 ngày</h3>
                </div>
                <div class="card-body">
                    <canvas id="readTrendChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cart-shopping mr-1"></i> Truyện được mua nhiều nhất tháng này</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Truyện</th>
                                    <th class="text-center" width="110">Mở khóa</th>
                                    <th class="text-center" width="110">Người mua</th>
                                    <th class="text-center" width="110">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPurchasedArticles as $article)
                                    <tr>
                                        <td>
                                            <strong class="analytics-story-title">{{ $article->title }}</strong>
                                            <div class="text-muted small">{{ number_format($article->view) }} lượt xem tổng</div>
                                        </td>
                                        <td class="text-center"><span class="badge badge-primary">{{ number_format($article->unlocks_count) }}</span></td>
                                        <td class="text-center"><span class="badge badge-info">{{ number_format($article->buyers_count) }}</span></td>
                                        <td class="text-center"><span class="badge badge-warning">{{ number_format($article->credits_spent) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Chưa có lượt mua bằng credit trong tháng này</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-eye mr-1"></i> Truyện được xem nhiều nhất tháng này</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Truyện</th>
                                    <th class="text-center" width="110">Lượt đọc</th>
                                    <th class="text-center" width="110">Người đọc</th>
                                    <th class="text-center" width="110">View tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topViewedArticles as $article)
                                    <tr>
                                        <td><strong class="analytics-story-title">{{ $article->title }}</strong></td>
                                        <td class="text-center"><span class="badge badge-primary">{{ number_format($article->reads_count) }}</span></td>
                                        <td class="text-center"><span class="badge badge-info">{{ number_format($article->readers_count) }}</span></td>
                                        <td class="text-center"><span class="badge badge-secondary">{{ number_format($article->view) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Chưa có lịch sử đọc trong tháng này</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-ranking-star mr-1"></i> Top view tổng</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($topAllTimeViewedArticles as $article)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('admin.articles.edit', $article->id) }}" class="info-box shadow-sm text-dark" style="text-decoration:none">
                            <span class="info-box-icon bg-light"><i class="fas fa-book-open text-primary"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text analytics-story-title">{{ $article->title }}</span>
                                <span class="info-box-number">{{ number_format($article->view) }} view</span>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-3">Chưa có dữ liệu view</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const labels = @json($chartLabels);
        const purchaseData = @json($purchaseChartData);
        const creditData = @json($creditChartData);
        const readData = @json($readChartData);

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            legend: { position: 'bottom' },
            scales: {
                yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
            }
        };

        new Chart(document.getElementById('purchaseTrendChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Người mua',
                        data: purchaseData.buyers,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, .12)',
                        fill: true,
                        lineTension: 0.25
                    },
                    {
                        label: 'Giao dịch',
                        data: purchaseData.transactions,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, .1)',
                        fill: true,
                        lineTension: 0.25
                    }
                ]
            },
            options: baseOptions
        });

        new Chart(document.getElementById('creditTrendChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Lượt mở khóa',
                        data: creditData.unlocks,
                        backgroundColor: '#ffc107'
                    },
                    {
                        label: 'Người đọc trả credit',
                        data: creditData.readers,
                        backgroundColor: '#17a2b8'
                    }
                ]
            },
            options: baseOptions
        });

        new Chart(document.getElementById('readTrendChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Lượt đọc',
                        data: readData.read_events,
                        borderColor: '#6610f2',
                        backgroundColor: 'rgba(102, 16, 242, .1)',
                        fill: true,
                        lineTension: 0.25
                    },
                    {
                        label: 'Người đọc',
                        data: readData.readers,
                        borderColor: '#fd7e14',
                        backgroundColor: 'rgba(253, 126, 20, .08)',
                        fill: true,
                        lineTension: 0.25
                    }
                ]
            },
            options: baseOptions
        });
    })();
</script>
@endpush
