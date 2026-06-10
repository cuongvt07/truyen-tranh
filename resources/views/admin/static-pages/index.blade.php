@extends('layout.admin')

@section('template_title', 'Quản lý Trang tĩnh')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Trang tĩnh (Static Pages)</h3>
            <a href="{{ route('admin.static-pages.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm mới
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="type" class="form-control form-control-sm">
                        <option value="">Tất cả loại</option>
                        @foreach(\App\Models\StaticPage::TYPES as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(\App\Models\StaticPage::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm theo slug hoặc tiêu đề..." 
                               value="{{ $filters['search'] ?? '' }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="default" {{ ($filters['sort'] ?? 'default') == 'default' ? 'selected' : '' }}>Mặc định</option>
                        <option value="title" {{ ($filters['sort'] ?? '') == 'title' ? 'selected' : '' }}>Tiêu đề A-Z</option>
                        <option value="views" {{ ($filters['sort'] ?? '') == 'views' ? 'selected' : '' }}>Lượt xem</option>
                        <option value="id_desc" {{ ($filters['sort'] ?? '') == 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.static-pages.index') }}" class="btn btn-sm btn-secondary btn-block">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                </div>
            </div>
        </form>

        {{-- Stats --}}
        <p class="text-muted small mb-2">
            Hiển thị {{ $pages->firstItem() ?? 0 }}-{{ $pages->lastItem() ?? 0 }} trong tổng số {{ $pages->total() }} trang
        </p>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Trang</th>
                        <th width="100">Loại</th>
                        <th width="130">Trang cha</th>
                        <th width="80" class="text-center">Comments</th>
                        <th width="70" class="text-center">Ghim</th>
                        <th width="80" class="text-center">Lượt xem</th>
                        <th width="90" class="text-center">Hiển thị</th>
                        <th width="90" class="text-center">Duyệt</th>
                        <th width="60" class="text-center">STT</th>
                        <th width="100" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td class="text-muted">{{ $page->id }}</td>
                            <td>
                                <strong>{{ $page->title_en }}</strong>
                                @if($page->title_vi)
                                    <div class="text-muted small">{{ $page->title_vi }}</div>
                                @endif
                                <code class="small">{{ $page->slug }}</code>
                            </td>
                            <td><span class="badge badge-secondary">{{ \App\Models\StaticPage::TYPES[$page->page_type] ?? $page->page_type }}</span></td>
                            <td>
                                @if($page->parent)
                                    <small>{{ Str::limit($page->parent->title_en, 20) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($page->comments_enabled)
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($page->is_pinned)
                                    <i class="fas fa-thumbtack text-warning"></i>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($page->view_count ?? 0) }}</td>
                            <td class="text-center">
                                @if($page->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Hidden</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(isset($page->status))
                                    @php $statusBadge = ['pending'=>'warning','approved'=>'success','rejected'=>'danger']; @endphp
                                    <span class="badge badge-{{ $statusBadge[$page->status] ?? 'secondary' }}">
                                        {{ \App\Models\StaticPage::STATUSES[$page->status] ?? $page->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">{{ $page->sort_order }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.static-pages.edit', $page) }}" 
                                   class="btn btn-sm btn-info" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.static-pages.destroy', $page) }}" 
                                      method="POST" class="d-inline formDelete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btnDelete" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">Không tìm thấy trang nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $pages->links() }}
        </div>
    </div>
</div>
@endsection
