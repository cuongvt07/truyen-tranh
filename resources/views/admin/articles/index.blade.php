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
                <select name="source" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">Nguồn</option>
                    <option value="user" @selected(request('source')==='user')>User gửi</option>
                    <option value="admin" @selected(request('source')==='admin')>Admin tạo</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="newest" @selected(request('sort')==='newest')>Mới nhất</option>
                    <option value="updated" @selected(request('sort')==='updated')>Cập nhật</option>
                    <option value="views" @selected(request('sort')==='views')>Lượt xem</option>
                    <option value="interest" @selected(request('sort')==='interest')>Nhiều quan tâm</option>
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
                    <th width="90" class="text-center">Quan tâm</th>
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
                                <div class="d-flex align-items-center" style="gap:6px">
                                    {{-- Bấm tên truyện = vào danh sách chương để sửa --}}
                                    <a href="{{ route('admin.articles.show_chapters', $article->id) }}" class="font-weight-600 text-truncate" style="max-width:230px" title="Xem danh sách chương">{{ $article->title }}</a>
                                    {{-- Icon mắt = mở trang truyện ngoài trang chủ --}}
                                    <a href="{{ route('articles.show', $article) }}" target="_blank" class="text-muted" title="Xem ngoài trang chủ" style="flex-shrink:0"><i class="fas fa-eye"></i></a>
                                </div>
                                <small class="text-muted">
                                    @foreach($article->authors->take(2) as $a){{ $a->name }}@if(!$loop->last), @endif @endforeach
                                </small>
                                @if($article->is_user_submitted)
                                    <div><span class="badge badge-info badge-pill" title="Truyện do user gửi"><i class="fas fa-user mr-1"></i>{{ optional($article->user)->name ?? 'User' }}</span></div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @foreach($article->genres->take(3) as $g)<span class="badge badge-success badge-pill">{{ $g->name }}</span> @endforeach
                        @if($article->genres->count() > 3)<span class="text-muted small">+{{ $article->genres->count()-3 }}</span>@endif
                    </td>
                    <td class="text-center"><a href="{{ route('admin.articles.show_chapters', $article->id) }}">{{ $article->chapters_count }}</a></td>
                    <td class="text-center">{{ number_format($article->view) }}</td>
                    <td class="text-center"><span class="badge badge-{{ $article->bookmarks_count > 0 ? 'danger' : 'light' }} badge-pill"><i class="fas fa-heart"></i> {{ number_format($article->bookmarks_count) }}</span></td>
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
                                <a class="dropdown-item" href="{{ route('articles.show', $article) }}" target="_blank"><i class="fas fa-eye mr-2 text-muted"></i> Xem</a>
                                <a class="dropdown-item" href="{{ route('admin.articles.edit', $article->id) }}"><i class="fas fa-edit mr-2 text-primary"></i> Sửa</a>
                                <a class="dropdown-item" href="{{ route('admin.articles.create_chapter', $article->id) }}"><i class="fas fa-plus mr-2 text-info"></i> Thêm chương</a>
                                <a class="dropdown-item" href="{{ route('admin.articles.show_chapters', $article->id) }}"><i class="fas fa-list-ol mr-2 text-secondary"></i> Danh sách chương</a>
                                @if($currentUser->is_admin)
                                    <div class="dropdown-divider"></div>
                                    @if($article->status == ArticleStatus::PENDING->value)
                                        <form action="{{ route('admin.articles.change_status', [$article->id, ArticleStatus::APPROVED]) }}" method="POST">@csrf @method('PATCH')
                                            <button class="dropdown-item text-success"><i class="fas fa-check mr-2"></i> Duyệt bài</button></form>
                                        <form action="{{ route('admin.articles.change_status', [$article->id, ArticleStatus::HIDDEN]) }}" method="POST">@csrf @method('PATCH')
                                            <button class="dropdown-item text-danger"><i class="fas fa-times mr-2"></i> Từ chối</button></form>
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
