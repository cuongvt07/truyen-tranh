@extends('layout.admin')
@section('template_title', 'Báo cáo lỗi chương')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    {{-- Stats ngang --}}
    <div class="row mb-3">
        <div class="col-sm-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-secondary"><i class="fas fa-flag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tổng báo cáo</span>
                    <span class="info-box-number">{{ number_format($totalAll) }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Chưa xử lý</span>
                    <span class="info-box-number">{{ number_format($openTotal) }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Đã xử lý</span>
                    <span class="info-box-number">{{ number_format($resolvedTotal) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap" style="gap:8px">
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('admin.chapter_reports.index', ['filter' => 'open']) }}"
                   class="btn {{ $onlyOpen ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="fas fa-exclamation-circle"></i> Chưa xử lý
                    <span class="badge badge-light ml-1">{{ number_format($openTotal) }}</span>
                </a>
                <a href="{{ route('admin.chapter_reports.index', ['filter' => 'all']) }}"
                   class="btn {{ !$onlyOpen ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-list"></i> Tất cả
                </a>
            </div>
            <span class="text-muted small">Nhóm theo chương — nhiều chưa xử lý nhất lên đầu</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0 table-responsive">
            @forelse($reports as $r)
            @if($loop->first)
            <table class="table table-hover mb-0 align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>Truyện / Chương</th>
                        <th width="130" class="text-center">
                            <span class="text-danger"><i class="fas fa-times-circle"></i> Chưa xử lý</span>
                        </th>
                        <th width="130" class="text-center">
                            <span class="text-success"><i class="fas fa-check-circle"></i> Đã xử lý</span>
                        </th>
                        <th width="70" class="text-center text-muted">Tổng</th>
                        <th width="130">Lần cuối</th>
                        <th width="160" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
            @endif
                <tr class="{{ $r->open_count == 0 ? 'table-light text-muted' : '' }}">
                    <td>
                        <div class="font-weight-bold">
                            @if($r->article)
                                <a href="{{ route('articles.show', $r->article_id) }}" target="_blank">
                                    {{ \Illuminate\Support\Str::limit($r->article->title, 55) }}
                                </a>
                            @else
                                <span class="text-danger small">(truyện đã xoá)</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">
                            @if($r->chapter)
                                <a href="{{ route('articles.chapters.show', [$r->article_id, $r->chapter->number]) }}" target="_blank" class="text-secondary">
                                    <i class="fas fa-book-open fa-xs mr-1"></i>Chương {{ $r->chapter->number }}{{ $r->chapter->title ? ' — ' . \Illuminate\Support\Str::limit($r->chapter->title, 35) : '' }}
                                </a>
                            @else
                                <span>(chương đã xoá)</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">
                        @if($r->open_count > 0)
                            <span class="badge badge-danger px-2 py-1" style="font-size:13px">{{ $r->open_count }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($r->resolved_count > 0)
                            <span class="badge badge-success px-2 py-1" style="font-size:13px">{{ $r->resolved_count }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center small text-muted">{{ $r->total }}</td>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($r->last_reported_at)->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        @if($r->open_count > 0)
                            <form action="{{ route('admin.chapter_reports.resolve_chapter') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="chapter_id" value="{{ $r->chapter_id }}">
                                <button class="btn btn-sm btn-success" title="Xử lý tất cả báo cáo của chương này">
                                    <i class="fas fa-check-double"></i> Xử lý tất cả
                                </button>
                            </form>
                        @else
                            <span class="badge badge-success"><i class="fas fa-check"></i> Hoàn tất</span>
                        @endif
                    </td>
                </tr>
                @if($loop->last)</tbody></table>@endif
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-flag-checkered fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Không có báo cáo nào{{ $onlyOpen ? ' chưa xử lý' : '' }}</h5>
                </div>
            @endforelse
        </div>
        @if($reports->hasPages())
            <div class="card-footer">{{ $reports->links() }}</div>
        @endif
    </div>

</div></div>
@endsection
