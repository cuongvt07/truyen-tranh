@extends('layout.admin')
@section('template_title', 'Bình luận')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2">
        <form method="GET" class="form-row align-items-center">
            <div class="col-md-4 mb-2"><div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control" placeholder="Tìm nội dung bình luận..." value="{{ request('q') }}">
                <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
            </div></div>
            <div class="col-md-4 mb-2">
                <select name="article_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">— Tất cả truyện —</option>
                    @foreach($articles as $a)<option value="{{ $a->id }}" @selected(request('article_id')==$a->id)>{{ \Illuminate\Support\Str::limit($a->title, 50) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2"><a href="{{ route('admin.comments.index') }}" class="btn btn-sm btn-outline-secondary btn-block"><i class="fas fa-times"></i> Xoá lọc</a></div>
        </form>
    </div></div>

    <form method="post" action="{{ route('admin.comments.bulk_destroy') }}" id="bulk-form" onsubmit="return confirm('Xoá các bình luận đã chọn?')">
        @csrf
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="text-muted small">Tổng {{ number_format($total) }} bình luận</span>
                <button type="submit" class="btn btn-sm btn-danger" id="bulk-del-btn" style="display:none"><i class="fas fa-trash"></i> Xoá đã chọn (<span id="sel-count">0</span>)</button>
            </div>
            <div class="card-body p-0 table-responsive">
                @forelse($comments as $c)
                @if($loop->first)
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th width="36"><input type="checkbox" id="check-all"></th>
                        <th>Nội dung</th><th width="150">Người dùng</th><th>Truyện</th><th width="110">Thời gian</th><th width="70" class="text-center">Xoá</th>
                    </tr></thead><tbody>
                @endif
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $c->id }}" class="row-check"></td>
                        <td>{{ \Illuminate\Support\Str::limit($c->content, 120) }}</td>
                        <td class="text-muted small">{{ optional($c->user)->name ?? optional($c->user)->username ?? 'Ẩn danh' }}</td>
                        <td class="small">@if($c->article)<a href="{{ route('articles.show', $c->article_id) }}" target="_blank">{{ \Illuminate\Support\Str::limit($c->article->title, 36) }}</a>@else — @endif</td>
                        <td class="text-muted small">{{ optional($c->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Xoá"
                                onclick="if(confirm('Xoá bình luận này?')){document.getElementById('del-{{ $c->id }}').submit();}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @if($loop->last)</tbody></table>@endif
                @empty
                    <div class="text-center py-5"><i class="fas fa-comments fa-3x text-muted mb-3"></i><h5 class="text-muted">Chưa có bình luận nào</h5></div>
                @endforelse
            </div>
            @if($comments->hasPages())<div class="card-footer">{{ $comments->withQueryString()->links() }}</div>@endif
        </div>
    </form>

    {{-- Forms xoá đơn lẻ (ngoài bulk-form để tránh lồng form) --}}
    @foreach($comments as $c)
        <form id="del-{{ $c->id }}" method="post" action="{{ route('admin.comments.destroy', $c->id) }}" class="d-none">@csrf @method('delete')</form>
    @endforeach
</div></div>

<script>
(function(){
    var all = document.getElementById('check-all');
    var btn = document.getElementById('bulk-del-btn');
    var cnt = document.getElementById('sel-count');
    function refresh(){
        var n = document.querySelectorAll('.row-check:checked').length;
        cnt.textContent = n;
        btn.style.display = n > 0 ? '' : 'none';
    }
    all && all.addEventListener('change', function(){
        document.querySelectorAll('.row-check').forEach(c => c.checked = all.checked);
        refresh();
    });
    document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', refresh));
})();
</script>
@endsection
