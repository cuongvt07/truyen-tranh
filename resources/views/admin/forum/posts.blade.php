@extends('layout.admin')
@section('template_title', 'Duyệt bài Forum')

@section('content')
<div class="content"><div class="container-fluid">
    @include('admin.partials.flash')

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ !request('status') ? 'active' : '' }}"
               href="{{ route('admin.forum.posts.index') }}">Tất cả</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'pending' ? 'active' : '' }}"
               href="{{ route('admin.forum.posts.index', ['status' => 'pending']) }}">
                Chờ duyệt
                @php $pendingCount = \App\Models\StaticPage::where('page_type','forum_post')->where('status','pending')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="badge badge-warning">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'approved' ? 'active' : '' }}"
               href="{{ route('admin.forum.posts.index', ['status' => 'approved']) }}">Đã duyệt</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('status') === 'rejected' ? 'active' : '' }}"
               href="{{ route('admin.forum.posts.index', ['status' => 'rejected']) }}">Từ chối</a>
        </li>
        <li class="nav-item ml-auto">
            <a class="nav-link {{ request()->routeIs('admin.forum.comments.index') ? 'active' : '' }}"
               href="{{ route('admin.forum.comments.index') }}">
                <i class="fas fa-comments"></i> Bình luận Forum
            </a>
        </li>
    </ul>

    {{-- Filter --}}
    <div class="card card-outline card-primary mb-3"><div class="card-body py-2">
        <form method="GET" class="form-row align-items-center">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="col-md-4 mb-2">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Tìm tiêu đề bài..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="category" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">— Tất cả chuyên mục —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>
                            {{ $cat->title_en }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button class="btn btn-sm btn-primary btn-block">
                    <i class="fas fa-search"></i> Lọc
                </button>
            </div>
            <div class="col-md-2 mb-2">
                <a href="{{ route('admin.forum.posts.index', array_filter(['status' => request('status')])) }}"
                   class="btn btn-sm btn-outline-secondary btn-block">Reset</a>
            </div>
        </form>
    </div></div>

    <div class="card">
        <div class="card-header">
            <span class="text-muted small">{{ $posts->total() }} bài viết</span>
        </div>
        <div class="card-body p-0 table-responsive">
            @forelse($posts as $p)
                @if($loop->first)
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th width="130">Tác giả</th>
                            <th width="140">Chuyên mục</th>
                            <th width="90">Trạng thái</th>
                            <th width="110">Ngày đăng</th>
                            <th width="160" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                @endif
                    <tr class="{{ $p->status === 'pending' ? 'table-warning' : ($p->status === 'rejected' ? 'table-danger' : '') }}">
                        <td>
                            <strong>{{ $p->title_en }}</strong>
                            @if($p->title_vi)
                                <div class="text-muted small">{{ $p->title_vi }}</div>
                            @endif
                            <code class="small">{{ $p->slug }}</code>
                        </td>
                        <td class="small">
                            @if($p->author)
                                <a href="{{ route('admin.users.show', $p->author) }}" target="_blank">
                                    {{ $p->author->name ?? $p->author->username }}
                                </a>
                            @else
                                <span class="text-muted">Admin</span>
                            @endif
                        </td>
                        <td class="small">
                            {{ optional($p->parent)->title_en ?? '—' }}
                        </td>
                        <td>
                            @php
                                $badge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                $label = \App\Models\StaticPage::STATUSES[$p->status] ?? $p->status;
                            @endphp
                            <span class="badge badge-{{ $badge[$p->status] ?? 'secondary' }}">{{ $label }}</span>
                        </td>
                        <td class="text-muted small">{{ optional($p->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            @if($p->status !== 'approved')
                                <form method="POST" action="{{ route('admin.forum.posts.approve', $p) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-xs btn-success" title="Duyệt">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                </form>
                            @endif
                            @if($p->status !== 'rejected')
                                <form method="POST" action="{{ route('admin.forum.posts.reject', $p) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-xs btn-warning" title="Từ chối">
                                        <i class="fas fa-ban"></i> Từ chối
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.static-pages.edit', $p) }}" class="btn btn-xs btn-outline-primary" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.forum.posts.destroy', $p) }}" class="d-inline"
                                  onsubmit="return confirm('Xoá bài viết này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger" title="Xoá">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @if($loop->last)</tbody></table>@endif
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Không có bài viết nào.</h5>
                </div>
            @endforelse
        </div>
        @if($posts->hasPages())
            <div class="card-footer">{{ $posts->withQueryString()->links() }}</div>
        @endif
    </div>
</div></div>
@endsection
