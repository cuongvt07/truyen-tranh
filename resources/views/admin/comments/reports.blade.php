@extends('layout.admin')
@section('template_title', 'Báo cáo bình luận')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.comment_reports.index', ['filter' => 'open']) }}"
               class="btn {{ $onlyOpen ? 'btn-primary' : 'btn-outline-primary' }}">
                Chưa xử lý <span class="badge badge-light">{{ number_format($openTotal) }}</span>
            </a>
            <a href="{{ route('admin.comment_reports.index', ['filter' => 'all']) }}"
               class="btn {{ $onlyOpen ? 'btn-outline-secondary' : 'btn-secondary' }}">Tất cả</a>
        </div>
        <span class="text-muted small">Sắp xếp: báo cáo chưa xử lý nhiều nhất lên đầu</span>
    </div></div>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            @forelse($comments as $c)
            @if($loop->first)
            <table class="table table-hover mb-0 align-middle">
                <thead><tr>
                    <th>Bình luận</th>
                    <th width="150">Người viết</th>
                    <th width="160">Truyện</th>
                    <th width="240">Lý do báo cáo</th>
                    <th width="90" class="text-center">Số báo cáo</th>
                    <th width="150" class="text-center">Thao tác</th>
                </tr></thead><tbody>
            @endif
                <tr class="{{ $c->open_reports_count > 0 ? '' : 'text-muted' }}">
                    <td>
                        <div style="max-width:340px">{{ \Illuminate\Support\Str::limit($c->content, 160) }}</div>
                        <small class="text-muted">#{{ $c->id }} · {{ optional($c->created_at)->format('d/m/Y H:i') }}</small>
                    </td>
                    <td class="small">{{ optional($c->user)->name ?? optional($c->user)->username ?? 'Ẩn danh' }}</td>
                    <td class="small">
                        @if($c->article)
                            <a href="{{ route('articles.show', $c->article_id) }}#comment-{{ $c->id }}" target="_blank">{{ \Illuminate\Support\Str::limit($c->article->title, 30) }}</a>
                        @else — @endif
                    </td>
                    <td class="small">
                        @foreach($c->reports->take(5) as $r)
                            <div class="text-truncate" style="max-width:230px" title="{{ $r->reason }}">
                                @if($r->resolved)<i class="fas fa-check text-success" title="Đã xử lý"></i>@else<i class="fas fa-flag text-danger"></i>@endif
                                {{ $r->reason ? \Illuminate\Support\Str::limit($r->reason, 40) : '(không ghi lý do)' }}
                                <span class="text-muted">— {{ optional($r->user)->name ?? optional($r->user)->username ?? 'ẩn danh' }}</span>
                            </div>
                        @endforeach
                        @if($c->reports->count() > 5)<small class="text-muted">+{{ $c->reports->count() - 5 }} báo cáo khác</small>@endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-danger badge-pill">{{ $c->reports_count }}</span>
                        @if($c->open_reports_count > 0)<br><small class="text-danger">{{ $c->open_reports_count }} chưa xử lý</small>@endif
                    </td>
                    <td class="text-center">
                        @if($c->open_reports_count > 0)
                        <form action="{{ route('admin.comment_reports.resolve', $c->id) }}" method="POST" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-success" title="Đánh dấu đã xử lý"><i class="fas fa-check"></i> Bỏ qua</button>
                        </form>
                        @endif
                        <form action="{{ route('admin.comments.destroy', $c->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Xoá bình luận này (kèm toàn bộ báo cáo)?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Xoá bình luận"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @if($loop->last)</tbody></table>@endif
            @empty
                <div class="text-center py-5"><i class="fas fa-flag-checkered fa-3x text-muted mb-3"></i><h5 class="text-muted">Không có báo cáo nào{{ $onlyOpen ? ' chưa xử lý' : '' }}</h5></div>
            @endforelse
        </div>
        @if($comments->hasPages())<div class="card-footer">{{ $comments->links() }}</div>@endif
    </div>
</div></div>
@endsection
