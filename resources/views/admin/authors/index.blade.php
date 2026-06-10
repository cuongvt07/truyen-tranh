@extends('layout.admin')

@section('template_title', 'Quản lý Tác giả')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Tác giả (Authors)</h3>
            <a href="{{ route('admin.authors.create') }}" class="btn btn-primary btn-sm">
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
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm kiếm theo tên hoặc mô tả..." 
                               value="{{ $filters['search'] ?? '' }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="name" {{ ($filters['sort'] ?? 'name') == 'name' ? 'selected' : '' }}>Tên A-Z</option>
                        <option value="articles_count" {{ ($filters['sort'] ?? '') == 'articles_count' ? 'selected' : '' }}>Số truyện nhiều nhất</option>
                        <option value="id_desc" {{ ($filters['sort'] ?? '') == 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="id_asc" {{ ($filters['sort'] ?? '') == 'id_asc' ? 'selected' : '' }}>Cũ nhất</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.authors.index') }}" class="btn btn-sm btn-secondary btn-block">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                </div>
            </div>
        </form>

        {{-- Stats --}}
        <p class="text-muted small mb-2">
            Hiển thị {{ $authors->firstItem() ?? 0 }}-{{ $authors->lastItem() ?? 0 }} trong tổng số {{ $authors->total() }} tác giả
        </p>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Tên tác giả</th>
                        <th>Mô tả</th>
                        <th width="100" class="text-center">Số truyện</th>
                        <th width="120" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($authors as $author)
                        <tr>
                            <td class="text-muted">{{ $author->id }}</td>
                            <td>
                                <strong>{{ $author->name }}</strong>
                                <a href="{{ route('authors.show', $author->id) }}" 
                                   class="btn btn-xs btn-link" 
                                   target="_blank" 
                                   title="Xem trang công khai">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                            <td>{{ Str::limit($author->description, 80) ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $author->articles_count ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.authors.edit', $author->id) }}" 
                                   class="btn btn-sm btn-info" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.authors.destroy', $author->id) }}" 
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
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">Không tìm thấy tác giả nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $authors->links() }}
        </div>
    </div>
</div>
@endsection
