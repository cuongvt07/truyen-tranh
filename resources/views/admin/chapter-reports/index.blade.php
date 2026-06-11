@extends('layout.admin')
@section('template_title', 'Báo cáo lỗi chương')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap">
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.chapter_reports.index', ['filter' => 'open']) }}"
               class="btn {{ $onlyOpen ? 'btn-primary' : 'btn-outline-primary' }}">
                Chưa xử lý <span class="badge badge-light">{{ number_format($openTotal) }}</span>
            </a>
            <a href="{{ route('admin.chapter_reports.index', ['filter' => 'all']) }}"
               class="btn {{ $onlyOpen ? 'btn-outline-secondary' : 'btn-secondary' }}">Tất cả</a>
        </div>
        <span class="text-muted small">Sắp xếp: báo cáo mới nhất lên đầu</span>
    </div></div>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            @forelse($reports as $r)
            @if($loop->first)
            <table class="table table-hover mb-0 align-middle">
                <thead><tr>
                    <th width="260">Chương</th>
                    <th width="160">Truyện</th>
                    <th>Lý do báo cáo</th>
                    <th width="150">Người báo cáo</th>
                    <th width="140">Thời gian</th>
                    <th width="150" class="text-center">Thao tác</th>
                </tr></thead><tbody>
            @endif
                <tr class="{{ $r->resolved ? 'text-muted' : '' }}">
                    <td>
                        @if($r->chapter && $r->article)
                            <a href="{{ route('articles.chapters.show', [$r->article_id, $r->chapter->number]) }}" target="_blank">
                                Chương {{ $r->chapter->number }} — {{ \Illuminate\Support\Str::limit($r->chapter->title, 30) }}
                            </a>
                        @else <span class="text-danger">(chương đã xoá)</span> @endif
                        <br><small class="text-muted">#{{ $r->id }}</small>
                        @if($r->resolved)<span class="badge badge-success">Đã xử lý</span>@else<span class="badge badge-danger">Chưa xử lý</span>@endif
                    </td>
                    <td class="small">
                        @if($r->article)
                            <a href="{{ route('articles.show', $r->article_id) }}" target="_blank">{{ \Illuminate\Support\Str::limit($r->article->title, 30) }}</a>
                        @else — @endif
                    </td>
                    <td class="small">{{ $r->reason ? e($r->reason) : '(không ghi lý do)' }}</td>
                    <td class="small">{{ optional($r->user)->name ?? optional($r->user)->username ?? 'Ẩn danh' }}</td>
                    <td class="small">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        @unless($r->resolved)
                        <form action="{{ route('admin.chapter_reports.resolve', $r->id) }}" method="POST" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-success" title="Đánh dấu đã xử lý"><i class="fas fa-check"></i></button>
                        </form>
                        @endunless
                        <form action="{{ route('admin.chapter_reports.destroy', $r->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Xoá báo cáo này?')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Xoá báo cáo"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @if($loop->last)</tbody></table>@endif
            @empty
                <div class="text-center py-5"><i class="fas fa-flag-checkered fa-3x text-muted mb-3"></i><h5 class="text-muted">Không có báo cáo nào{{ $onlyOpen ? ' chưa xử lý' : '' }}</h5></div>
            @endforelse
        </div>
        @if($reports->hasPages())<div class="card-footer">{{ $reports->links() }}</div>@endif
    </div>
</div></div>
@endsection
