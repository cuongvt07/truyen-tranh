@extends('layout.admin')
@section('template_title')
    {{ __('Trang tổng quan') }}
@endsection

@section('content')
    {{-- ===== CẦN XỬ LÝ ===== --}}
    <div class="mb-2"><h5 class="text-muted"><i class="fas fa-bell text-danger mr-1"></i> Cần xử lý</h5></div>
    <div class="row">
        @php
        $needCards = [
            [
                'icon'  => 'fas fa-hourglass-half',
                'color' => 'bg-warning',
                'label' => 'Truyện chờ duyệt',
                'open'  => $pendingArticles,
                'done'  => $approvedArticles,
                'url'   => route('admin.articles.index', ['status' => 0]),
                'link'  => 'Duyệt ngay',
            ],
            [
                'icon'  => 'fas fa-flag',
                'color' => 'bg-danger',
                'label' => 'Báo cáo bình luận',
                'open'  => $openReports,
                'done'  => $resolvedReports,
                'url'   => route('admin.comment_reports.index'),
                'link'  => 'Xem báo cáo',
            ],
            [
                'icon'  => 'fas fa-exclamation-triangle',
                'color' => 'bg-warning',
                'label' => 'Báo lỗi chương',
                'open'  => $openChapterReports,
                'done'  => $resolvedChapterReports,
                'url'   => route('admin.chapter_reports.index'),
                'link'  => 'Xem báo cáo',
            ],
            [
                'icon'  => 'fas fa-eye-slash',
                'color' => 'bg-secondary',
                'label' => 'Truyện đang ẩn',
                'open'  => $hiddenArticles,
                'done'  => $approvedArticles,
                'url'   => route('admin.articles.index', ['status' => 2]),
                'link'  => 'Xem',
            ],
        ];
        @endphp

        @foreach($needCards as $card)
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm h-100 mb-0" style="border-top:3px solid {{ $card['open'] ? '#dc3545' : '#6c757d' }}">
                <div class="card-body pb-2">
                    <div class="d-flex align-items-center mb-3">
                        <span class="{{ $card['color'] }} rounded p-2 mr-2" style="line-height:1">
                            <i class="{{ $card['icon'] }} text-white"></i>
                        </span>
                        <span class="font-weight-bold small text-uppercase text-muted">{{ $card['label'] }}</span>
                    </div>
                    <div class="d-flex align-items-stretch" style="gap:0">
                        <div class="flex-fill text-center px-2 py-1" style="border-right:1px solid #f0f0f0">
                            <div class="{{ $card['open'] ? 'text-danger' : 'text-muted' }} font-weight-bold" style="font-size:26px;line-height:1.1">
                                {{ number_format($card['open']) }}
                            </div>
                            <div class="text-muted" style="font-size:11px;margin-top:3px">Chưa xử lý</div>
                        </div>
                        <div class="flex-fill text-center px-2 py-1">
                            <div class="text-success font-weight-bold" style="font-size:26px;line-height:1.1">
                                {{ number_format($card['done']) }}
                            </div>
                            <div class="text-muted" style="font-size:11px;margin-top:3px">Đã xử lý</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-1 px-3" style="background:transparent">
                    <a href="{{ $card['url'] }}" class="small text-muted">
                        {{ $card['link'] }} <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
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
