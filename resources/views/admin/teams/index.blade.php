@extends('layout.admin')
@section('template_title', 'Nhóm dịch')

@section('content')
<div class="content">
    <div class="container-fluid">
        @includeWhen(session('success'), 'admin.partials.flash')
        
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-users text-primary"></i> Quản lý Nhóm dịch
            </h4>
            <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm nhóm dịch
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

                    {{-- Sort --}}
                    <div class="col-md-3 mb-2">
                        <select name="sort" class="form-control form-control-sm">
                            <option value="id_desc" {{ request('sort') === 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="id_asc" {{ request('sort') === 'id_asc' ? 'selected' : '' }}>Cũ nhất</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Tên A-Z</option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-5 mb-2 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        <a href="{{ route('admin.teams.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-redo"></i> Đặt lại
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-2 text-muted small">
            <i class="fas fa-info-circle"></i>
            Tổng: <strong>{{ $total }}</strong> nhóm dịch
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
                                <th width="80" class="text-center">Ảnh</th>
                                <th>Tên nhóm</th>
                                <th width="200">Website</th>
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
                                <td class="align-middle text-center">
                                    <img src="{{ $item->photo ?: asset('static/core/images/no_cover.webp') }}" 
                                         alt="{{ $item->name }}"
                                         style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold">{{ $item->name }}</div>
                                    @if($item->description)
                                        <div class="text-muted small text-truncate" style="max-width: 300px;">
                                            {{ Str::limit($item->description, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($item->site)
                                        <a href="{{ $item->site }}" target="_blank" class="text-muted small">
                                            <i class="fas fa-external-link-alt"></i>
                                            {{ Str::limit($item->site, 30) }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="text-muted small">
                                        <i class="fas fa-user"></i> {{ optional($item->user)->username ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.teams.edit', $item->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('admin.teams.destroy', $item->id) }}" 
                                              class="form-delete d-inline" 
                                              data-confirm="Bạn có chắc muốn xóa nhóm «{{ $item->name }}»?">
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
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có nhóm dịch nào</h5>
                        <p class="text-muted mb-3">Tạo nhóm dịch đầu tiên để quản lý các bản dịch</p>
                        <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm nhóm dịch
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
