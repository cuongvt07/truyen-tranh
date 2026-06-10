@extends('layout.admin')
@section('template_title', 'Bộ sưu tập')

@section('content')
<div class="content">
    <div class="container-fluid">
        @includeWhen(session('success'), 'admin.partials.flash')
        
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-layer-group text-primary"></i> Quản lý Bộ sưu tập
            </h4>
            <a href="{{ route('admin.collections.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm bộ sưu tập
            </a>
        </div>

        {{-- Filter Bar --}}
        <div class="card card-outline card-primary mb-3">
            <div class="card-body py-2">
                <form method="GET" class="form-row align-items-center">
                    {{-- Search --}}
                    <div class="col-md-4 mb-2">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" 
                                   name="q" 
                                   class="form-control" 
                                   placeholder="Tìm theo tên, mô tả..." 
                                   value="{{ request('q') }}">
                        </div>
                    </div>

                    {{-- Privacy Filter --}}
                    <div class="col-md-2 mb-2">
                        <select name="privacy" class="form-control form-control-sm">
                            <option value="">Tất cả hiển thị</option>
                            <option value="public" {{ request('privacy') === 'public' ? 'selected' : '' }}>Công khai</option>
                            <option value="private" {{ request('privacy') === 'private' ? 'selected' : '' }}>Riêng tư</option>
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div class="col-md-2 mb-2">
                        <select name="sort" class="form-control form-control-sm">
                            <option value="id_desc" {{ request('sort') === 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="id_asc" {{ request('sort') === 'id_asc' ? 'selected' : '' }}>Cũ nhất</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Tên A-Z</option>
                            <option value="articles_count" {{ request('sort') === 'articles_count' ? 'selected' : '' }}>Nhiều truyện nhất</option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-4 mb-2 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        <a href="{{ route('admin.collections.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-redo"></i> Đặt lại
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-2 text-muted small">
            <i class="fas fa-info-circle"></i>
            Tổng: <strong>{{ $total }}</strong> bộ sưu tập
            (Công khai: <strong>{{ $publicCount }}</strong>, Riêng tư: <strong>{{ $privateCount }}</strong>)
        </div>

        {{-- Table Card --}}
        <div class="card">
            <div class="card-body p-0">
                @forelse($items as $item)
                    @if($loop->first)
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="60">ID</th>
                                <th>Tên bộ sưu tập</th>
                                <th width="120" class="text-center">Số truyện</th>
                                <th width="100" class="text-center">Hiển thị</th>
                                <th width="140">Người tạo</th>
                                <th width="120" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                    @endif
                            <tr>
                                <td class="align-middle">
                                    <span class="badge badge-secondary">{{ $item->id }}</span>
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold">{{ $item->name }}</div>
                                    @if($item->description)
                                        <div class="text-muted small text-truncate" style="max-width: 400px;">
                                            {{ Str::limit($item->description, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-info badge-pill">
                                        <i class="fas fa-book"></i> {{ $item->articles_count }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    @if($item->is_private)
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-lock"></i> Riêng tư
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            <i class="fas fa-globe"></i> Công khai
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="text-muted small">
                                        <i class="fas fa-user"></i> {{ optional($item->user)->username ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.collections.edit', $item->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.collections.destroy', $item->id) }}" 
                                              class="form-delete d-inline" 
                                              data-confirm="Bạn có chắc muốn xóa bộ sưu tập «{{ $item->name }}»?">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                    @if($loop->last)
                        </tbody>
                    </table>
                    @endif
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-layer-group fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có bộ sưu tập nào</h5>
                        <p class="text-muted mb-3">Tạo bộ sưu tập đầu tiên để tổ chức truyện theo chủ đề</p>
                        <a href="{{ route('admin.collections.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm bộ sưu tập
                        </a>
                    </div>
                @endforelse
            </div>
            
            @if($items->hasPages())
                <div class="card-footer">
                    {{ $items->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
