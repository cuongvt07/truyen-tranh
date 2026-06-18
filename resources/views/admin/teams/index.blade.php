@extends('layout.admin')
@section('template_title', 'Nhóm dịch')

@section('content')
<div class="content">
    <div class="container-fluid">
        @includeWhen(session('success'), 'admin.partials.flash')
        @includeWhen(session('error'), 'admin.partials.flash-error')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-users text-primary"></i> Quản lý Nhóm dịch
            </h4>
            <div class="d-flex" style="gap:8px">
                @if($pendingCount > 0)
                <a href="{{ route('admin.teams.pending') }}" class="btn btn-warning">
                    <i class="fas fa-clock"></i> Duyệt yêu cầu
                    <span class="badge badge-light ml-1">{{ $pendingCount }}</span>
                </a>
                @endif
                <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm nhóm dịch
                </a>
            </div>
        </div>

        <div class="card card-outline card-primary mb-3">
            <div class="card-body py-2">
                <form method="GET" class="form-row align-items-center">
                    <div class="col-md-4 mb-2">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="q" class="form-control" placeholder="Tìm theo tên, mô tả..." value="{{ request('q') }}">
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="sort" class="form-control form-control-sm">
                            <option value="id_desc" {{ request('sort','id_desc')==='id_desc'?'selected':'' }}>Mới nhất</option>
                            <option value="id_asc"  {{ request('sort')==='id_asc'?'selected':'' }}>Cũ nhất</option>
                            <option value="name"    {{ request('sort')==='name'?'selected':'' }}>Tên A-Z</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Chờ duyệt</option>
                            <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Đã duyệt</option>
                            <option value="rejected" {{ request('status')==='rejected'?'selected':'' }}>Từ chối</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 text-right">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                        <a href="{{ route('admin.teams.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i> Đặt lại</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-2 text-muted small">
            <i class="fas fa-info-circle"></i> Tổng: <strong>{{ $total }}</strong> nhóm dịch
        </div>

        <div class="card">
            <div class="card-body p-0">
                @forelse($items as $item)
                    @if($loop->first)
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">ID</th>
                                <th width="70" class="text-center">Ảnh</th>
                                <th>Tên nhóm</th>
                                <th width="120" class="text-center">Trạng thái</th>
                                <th width="120" class="text-center">Thành viên</th>
                                <th width="100" class="text-center">Truyện</th>
                                <th width="130">Người tạo</th>
                                <th width="190" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                    @endif
                        <tr>
                            <td class="align-middle"><span class="badge badge-secondary">{{ $item->id }}</span></td>
                            <td class="align-middle text-center">
                                <img src="{{ $item->photo ?: asset('static/core/images/no_cover.webp') }}"
                                     style="width:44px;height:44px;border-radius:8px;object-fit:cover">
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $item->name }}</div>
                                @if($item->site)
                                    <a href="{{ $item->site }}" target="_blank" class="text-muted small">
                                        <i class="fas fa-external-link-alt"></i> {{ \Str::limit($item->site, 30) }}
                                    </a>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($item->isPending())
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Chờ duyệt</span>
                                @elseif($item->isRejected())
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Từ chối</span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Đã duyệt</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                <a href="{{ route('admin.teams.members', $item->id) }}" class="badge badge-info" style="font-size:12px">
                                    {{ $item->approved_members_count }} thành viên
                                    @if($item->pending_members_count > 0)
                                        <span class="badge badge-warning ml-1">{{ $item->pending_members_count }} chờ</span>
                                    @endif
                                </a>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-secondary">{{ $item->articles_count }} truyện</span>
                            </td>
                            <td class="align-middle">
                                <span class="text-muted small"><i class="fas fa-user"></i> {{ optional($item->user)->username ?? 'N/A' }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="btn-group btn-group-sm">
                                    @if($item->isPending())
                                        <form method="POST" action="{{ route('admin.teams.approve', $item->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Duyệt nhóm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.teams.reject', $item->id) }}" class="d-inline"
                                              onsubmit="return confirm('Từ chối nhóm «{{ $item->name }}»?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning" title="Từ chối nhóm">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.teams.members', $item->id) }}" class="btn btn-outline-info" title="Thành viên">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <a href="{{ route('admin.teams.edit', $item->id) }}" class="btn btn-outline-primary" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.teams.destroy', $item->id) }}"
                                          class="form-delete d-inline" data-confirm="Xoá nhóm «{{ $item->name }}»?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Xoá"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @if($loop->last)</tbody></table>@endif
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có nhóm dịch nào</h5>
                        <a href="{{ route('admin.teams.create') }}" class="btn btn-primary mt-2"><i class="fas fa-plus"></i> Thêm nhóm dịch</a>
                    </div>
                @endforelse
            </div>
            @if($items->hasPages())
                <div class="card-footer">{{ $items->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
