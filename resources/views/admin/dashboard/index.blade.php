@extends('layout.admin')
@section('template_title')
    {{ __('Trang tổng quan') }}
@endsection

@section('content')
    {{-- ===== CẦN XỬ LÝ ===== --}}
    <div class="mb-2"><h5 class="text-muted"><i class="fas fa-bell text-danger mr-1"></i> Cần xử lý</h5></div>
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box {{ $pendingArticles ? 'bg-warning' : 'bg-light' }}">
                <div class="inner">
                    <h3>{{ number_format($pendingArticles) }}</h3>
                    <p>Truyện chờ duyệt</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                <a href="{{ route('admin.articles.index', ['status' => 0]) }}" class="small-box-footer">Duyệt ngay <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box {{ $openReports ? 'bg-danger' : 'bg-light' }}">
                <div class="inner">
                    <h3>{{ number_format($openReports) }}</h3>
                    <p>Báo cáo bình luận chưa xử lý</p>
                </div>
                <div class="icon"><i class="fas fa-flag"></i></div>
                <a href="{{ route('admin.comment_reports.index') }}" class="small-box-footer">Xem báo cáo <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box {{ $hiddenArticles ? 'bg-secondary' : 'bg-light' }}">
                <div class="inner">
                    <h3>{{ number_format($hiddenArticles) }}</h3>
                    <p>Truyện đang bị ẩn</p>
                </div>
                <div class="icon"><i class="fas fa-eye-slash"></i></div>
                <a href="{{ route('admin.articles.index', ['status' => 2]) }}" class="small-box-footer">Xem <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- ===== TỔNG QUAN ===== --}}
    <div class="mb-2 mt-2"><h5 class="text-muted"><i class="fas fa-chart-simple mr-1"></i> Tổng quan</h5></div>
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ number_format($articleCount) }}</h3><p>Truyện</p></div>
                <div class="icon"><i class="ion ion-ios-book"></i></div>
                <a href="{{ route('admin.articles.index') }}" class="small-box-footer">Chi tiết <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ number_format($chapterCount) }}</h3><p>Chương</p></div>
                <div class="icon"><i class="fas fa-list-ol"></i></div>
                <a href="{{ route('admin.chapters.all') }}" class="small-box-footer">Chi tiết <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ number_format($commentCount) }}</h3><p>Bình luận</p></div>
                <div class="icon"><i class="fas fa-comments"></i></div>
                <a href="{{ route('admin.comments.index') }}" class="small-box-footer">Chi tiết <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ number_format($userCount) }}</h3><p>Tài khoản</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">Chi tiết <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- ===== THỐNG KÊ KHÁC ===== --}}
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-layer-group mr-1"></i> Thống kê khác</h3></div>
        <div class="card-body">
            <div class="row">
                @php
                    $others = [
                        ['VIP đang hoạt động', $vipActive, 'fa-crown', 'text-warning', route('admin.users.index')],
                        ['Tác giả', $authorCount, 'fa-pen', 'text-primary', route('admin.authors.index')],
                        ['Thể loại', $genreCount, 'fa-bars', 'text-info', route('admin.genres.index')],
                        ['Tags', $tagCount, 'fa-tags', 'text-secondary', route('admin.tags.index')],
                        ['Nhân vật', $characterCount, 'fa-user-pen', 'text-success', route('admin.characters.index')],
                        ['Nhóm dịch', $teamCount, 'fa-user-group', 'text-success', route('admin.teams.index')],
                        ['Bộ sưu tập', $collectionCount, 'fa-layer-group', 'text-success', route('admin.collections.index')],
                    ];
                @endphp
                @foreach($others as [$label, $val, $icon, $color, $url])
                    <div class="col-md-3 col-6 mb-3">
                        <a href="{{ $url }}" class="info-box shadow-sm text-dark" style="text-decoration:none">
                            <span class="info-box-icon bg-light"><i class="fas {{ $icon }} {{ $color }}"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">{{ $label }}</span>
                                <span class="info-box-number">{{ number_format($val) }}</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
