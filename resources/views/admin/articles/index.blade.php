@extends('layout.admin')
@section('template_title', 'Danh sách truyện')

@php
    use App\Enums\ArticleStatus;
    use App\Enums\ArticleCompleteStatus;
@endphp

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    {{-- Filter bar --}}
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2">
        <form method="GET" class="form-row align-items-center">
            <div class="col-md-3 mb-2"><div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control" placeholder="Tìm theo tên truyện..." value="{{ request('search') }}">
                <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
            </div></div>
            <div class="col-md-2 mb-2">
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">Trạng thái duyệt</option>
                    <option value="1" @selected(request('status')==='1')>Đã duyệt</option>
                    <option value="0" @selected(request('status')==='0')>Chờ duyệt</option>
                    <option value="2" @selected(request('status')==='2')>Đã ẩn</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="completed" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">Tiến độ</option>
                    <option value="0" @selected(request('completed')==='0')>Đang ra</option>
                    <option value="1" @selected(request('completed')==='1')>Hoàn thành</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="newest" @selected(request('sort')==='newest')>Mới nhất</option>
                    <option value="updated" @selected(request('sort')==='updated')>Cập nhật</option>
                    <option value="views" @selected(request('sort')==='views')>Lượt xem</option>
                    <option value="title" @selected(request('sort')==='title')>Tên A-Z</option>
                </select>
            </div>
            <div class="col-md-2 mb-2"><a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-outline-secondary btn-block"><i class="fas fa-times"></i> Xoá lọc</a></div>
        </form>
    </div></div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="text-muted small">Hiển thị {{ $articles->firstItem() ?? 0 }}–{{ $articles->lastItem() ?? 0 }} / {{ $articles->total() }} truyện</span>
            <a href="{{ route('admin.articles.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Thêm truyện mới</a>
        </div>
        <div class="card-body p-0 table-responsive">
            @forelse($articles as $article)
            @if($loop->first)
            <table class="table table-hover mb-0 align-middle">
                <thead><tr>
                    <th width="50">#</th>
                    <th>Truyện</th>
                    <th>Thể loại</th>
                    <th width="80" class="text-center">Chương</th>
                    <th width="90" class="text-center">Lượt xem</th>
                    <th width="150">Trạng thái</th>
                    <th width="100">Cập nhật</th>
                    <th width="70" class="text-center">Thao tác</th>
                </tr></thead>
                <tbody>
            @endif
                <tr>
                    <td class="text-muted">{{ $article->id }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ novel_poster($article) }}" style="width:44px;height:60px;object-fit:cover;border-radius:4px;flex-shrink:0;margin-right:10px">
                            <div style="min-width:0">
                                <a href="{{ route('articles.show', $article->id) }}" target="_blank" class="font-weight-600 d-block text-truncate" style="max-width:260px">{{ $article->title }}</a>
                                <small class="text-muted">
                                    @foreach($article->authors->take(2) as $a){{ $a->name }}@if(!$loop->last), @endif @endforeach
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @foreach($article->genres->take(3) as $g)<span class="badge badge-success badge-pill">{{ $g->name }}</span> @endforeach
                        @if($article->genres->count() > 3)<span class="text-muted small">+{{ $article->genres->count()-3 }}</span>@endif
                    </td>
                    <td class="text-center"><a href="{{ route('admin.articles.show_chapters', $article->id) }}">{{ $article->chapters_count }}</a></td>
                    <td class="text-center">{{ number_format($article->view) }}</td>
                    <td>
                        @php $st = [0=>['Chờ duyệt','warning'],1=>['Đã duyệt','success'],2=>['Đã ẩn','secondary']][$article->status] ?? ['?','secondary']; @endphp
                        <span class="badge badge-{{ $st[1] }} badge-pill">{{ $st[0] }}</span>
                        <span class="badge badge-{{ $article->is_completed ? 'info' : 'light' }} badge-pill">{{ $article->is_completed ? 'Full' : 'Đang ra' }}</span>
                    </td>
                    <td class="text-muted small" title="{{ $article->updated_at }}">{{ optional($article->updated_at)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('articles.show', $article->id) }}" target="_blank"><i class="fas fa-eye mr-2 text-muted"></i> Xem</a>
                                <a class="dropdown-item" href="{{ route('admin.articles.edit', $article->id) }}"><i class="fas fa-edit mr-2 text-primary"></i> Sửa</a>
                                <a class="dropdown-item" href="{{ route('admin.articles.create_chapter', $article->id) }}"><i class="fas fa-plus mr-2 text-info"></i> Thêm chương</a>
                                @if($currentUser->is_admin)
                                    <div class="dropdown-divider"></div>
                                    @if($article->status == ArticleStatus::PENDING->value)
                                        <form action="{{ route('admin.articles.change_status', [$article->id, ArticleStatus::APPROVED]) }}" method="POST">@csrf @method('PATCH')
                                            <button class="dropdown-item text-success"><i class="fas fa-check mr-2"></i> Duyệt bài</button></form>
                                    @elseif($article->status == ArticleStatus::HIDDEN->value)
                                        <form action="{{ route('admin.articles.change_status', [$article->id, ArticleStatus::APPROVED]) }}" method="POST">@csrf @method('PATCH')
                                            <button class="dropdown-item text-warning"><i class="fas fa-eye mr-2"></i> Hiện bài</button></form>
                                    @elseif($article->status == ArticleStatus::APPROVED->value)
                                        <form action="{{ route('admin.articles.change_status', [$article->id, ArticleStatus::HIDDEN]) }}" method="POST">@csrf @method('PATCH')
                                            <button class="dropdown-item"><i class="fas fa-eye-slash mr-2 text-muted"></i> Ẩn bài</button></form>
                                    @endif
                                    <form action="{{ route('admin.articles.change_complete_status', $article->id) }}" method="POST">@csrf @method('PATCH')
                                        <button class="dropdown-item"><i class="fas fa-flag-checkered mr-2 text-muted"></i> {{ $article->is_completed ? 'Đánh dấu đang ra' : 'Đánh dấu hoàn thành' }}</button></form>
                                @endif
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" class="form-delete" data-confirm="Xoá truyện «{{ $article->title }}»?">@csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i> Xoá</button></form>
                            </div>
                        </div>
                    </td>
                </tr>
                @if($loop->last)</tbody></table>@endif
            @empty
                <div class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><h5 class="text-muted">Không tìm thấy truyện nào</h5></div>
            @endforelse
        </div>
        @if($articles->hasPages())<div class="card-footer">{{ $articles->withQueryString()->links() }}</div>@endif
    </div>
</div></div>
@endsection
