@extends('layout.admin')
@section('template_title', 'Bình luận Forum / FAQ / Rules')

@section('content')
<div class="content"><div class="container-fluid">
    @include('admin.partials.flash')

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.forum.posts.index') }}">
                <i class="fas fa-newspaper"></i> Bài viết Forum
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.forum.comments.index') }}">
                <i class="fas fa-comments"></i> Bình luận
            </a>
        </li>
    </ul>

    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2">
        <form method="GET" class="form-row align-items-center">
            <div class="col-md-4 mb-2">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Tìm nội dung bình luận..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="page_type" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">— Tất cả loại trang —</option>
                    <option value="forum_post" @selected(request('page_type') === 'forum_post')>Forum Post</option>
                    <option value="faq_article" @selected(request('page_type') === 'faq_article')>FAQ Article</option>
                    <option value="rules" @selected(request('page_type') === 'rules')>Rules</option>
                    <option value="custom" @selected(request('page_type') === 'custom')>Custom</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button class="btn btn-sm btn-primary btn-block"><i class="fas fa-search"></i> Lọc</button>
            </div>
            <div class="col-md-2 mb-2">
                <a href="{{ route('admin.forum.comments.index') }}" class="btn btn-sm btn-outline-secondary btn-block">Reset</a>
            </div>
        </form>
    </div></div>

    <form method="POST" action="{{ route('admin.forum.comments.bulk_destroy') }}" id="bulk-form"
          onsubmit="return confirm('Xoá các bình luận đã chọn?')">
        @csrf
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="text-muted small">{{ $comments->total() }} bình luận</span>
                <button type="submit" class="btn btn-sm btn-danger" id="bulk-del-btn" style="display:none">
                    <i class="fas fa-trash"></i> Xoá đã chọn (<span id="sel-count">0</span>)
                </button>
            </div>
            <div class="card-body p-0 table-responsive">
                @forelse($comments as $c)
                    @if($loop->first)
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th width="36"><input type="checkbox" id="check-all"></th>
                            <th>Nội dung</th>
                            <th width="140">Người dùng</th>
                            <th width="180">Trang</th>
                            <th width="110">Thời gian</th>
                            <th width="70" class="text-center">Xoá</th>
                        </tr></thead>
                        <tbody>
                    @endif
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $c->id }}" class="row-check"></td>
                        <td>
                            @if($c->parent_id)
                                <span class="text-muted small"><i class="fa fa-reply"></i> Reply</span>
                            @endif
                            {{ \Illuminate\Support\Str::limit($c->content, 120) }}
                        </td>
                        <td class="text-muted small">
                            {{ optional($c->user)->name ?? optional($c->user)->username ?? 'Ẩn danh' }}
                        </td>
                        <td class="small">
                            @if($c->page)
                                <span class="badge badge-secondary">{{ $c->page->page_type }}</span>
                                {{ \Illuminate\Support\Str::limit($c->page->title_en, 40) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-muted small">{{ optional($c->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="if(confirm('Xoá bình luận này?')){document.getElementById('del-c-{{ $c->id }}').submit();}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @if($loop->last)</tbody></table>@endif
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có bình luận nào</h5>
                    </div>
                @endforelse
            </div>
            @if($comments->hasPages())
                <div class="card-footer">{{ $comments->withQueryString()->links() }}</div>
            @endif
        </div>
    </form>

    {{-- Forms xoá đơn lẻ --}}
    @foreach($comments as $c)
        <form id="del-c-{{ $c->id }}" method="POST" action="{{ route('admin.forum.comments.destroy', $c) }}" class="d-none">
            @csrf @method('DELETE')
        </form>
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
