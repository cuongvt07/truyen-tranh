@extends('layout.admin')

@section('template_title', 'Quản lý Nhân vật')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Nhân vật (Characters)</h3>
            <a href="{{ route('admin.characters.create') }}" class="btn btn-primary btn-sm">
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
                        <option value="0" {{ ($filters['type'] ?? '') === '0' ? 'selected' : '' }}>Nhân vật chính</option>
                        <option value="1" {{ ($filters['type'] ?? '') === '1' ? 'selected' : '' }}>Nhân vật phụ</option>
                        <option value="2" {{ ($filters['type'] ?? '') === '2' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm theo tên..." 
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
                        <option value="id_desc" {{ ($filters['sort'] ?? 'id_desc') == 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="name" {{ ($filters['sort'] ?? '') == 'name' ? 'selected' : '' }}>Tên A-Z</option>
                        <option value="articles_count" {{ ($filters['sort'] ?? '') == 'articles_count' ? 'selected' : '' }}>Số truyện</option>
                        <option value="id_asc" {{ ($filters['sort'] ?? '') == 'id_asc' ? 'selected' : '' }}>Cũ nhất</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.characters.index') }}" class="btn btn-sm btn-secondary btn-block">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                </div>
            </div>
        </form>

        {{-- Stats --}}
        <p class="text-muted small mb-2">
            Hiển thị {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} trong tổng số {{ $items->total() }} nhân vật
        </p>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="80" class="text-center">Ảnh</th>
                        <th>Tên nhân vật</th>
                        <th width="130">Loại</th>
                        <th width="100" class="text-center">Số truyện</th>
                        <th width="130">Người tạo</th>
                        <th width="120" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="text-muted">{{ $item->id }}</td>
                            <td class="text-center">
                                <img src="{{ $item->photo ?: asset('static/account/images/no-ava.jpg') }}" 
                                     alt="{{ $item->name }}"
                                     style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid #ddd">
                            </td>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                @if($item->description)
                                    <br><small class="text-muted">{{ Str::limit($item->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeBadge = ['primary', 'info', 'secondary'];
                                    $typeLabel = ['Nhân vật chính', 'Nhân vật phụ', 'Khác'];
                                @endphp
                                <span class="badge badge-{{ $typeBadge[$item->type] ?? 'secondary' }}">
                                    {{ $typeLabel[$item->type] ?? 'Khác' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $item->articles_count ?? 0 }}</span>
                            </td>
                            <td>
                                @if($item->user)
                                    <small>{{ $item->user->username }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('characters.show', $item) }}" target="_blank"
                                   class="btn btn-sm btn-secondary" title="Xem trang công khai">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.characters.edit', $item->id) }}"
                                   class="btn btn-sm btn-info" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.characters.destroy', $item->id) }}" 
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
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-user-friends fa-2x mb-2"></i>
                                <p class="mb-0">Chưa có nhân vật nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
