@extends('layout.admin')

@section('template_title', 'Quản lý Quảng cáo')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">Quảng cáo (Ads)</h3>
            <a href="{{ route('admin.ads.create') }}" class="btn btn-primary btn-sm">
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
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Đang bật</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Đang tắt</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="mode" class="form-control form-control-sm">
                        <option value="">Tất cả dạng</option>
                        @foreach(\App\Models\Ad::MODES as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['mode'] ?? '') === $val ? 'selected' : '' }}>{{ __('messages.ads.modes.'.$val) }}</option>
                        @endforeach
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
                <div class="col-md-2">
                    <select name="sort" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="priority" {{ ($filters['sort'] ?? 'priority') == 'priority' ? 'selected' : '' }}>Ưu tiên</option>
                        <option value="name" {{ ($filters['sort'] ?? '') == 'name' ? 'selected' : '' }}>Tên A-Z</option>
                        <option value="id_desc" {{ ($filters['sort'] ?? '') == 'id_desc' ? 'selected' : '' }}>Mới nhất</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.ads.index') }}" class="btn btn-sm btn-secondary btn-block">
                        <i class="fas fa-redo"></i> Đặt lại
                    </a>
                </div>
            </div>
        </form>

        {{-- Stats --}}
        <p class="text-muted small mb-2">
            Hiển thị {{ $ads->firstItem() ?? 0 }}-{{ $ads->lastItem() ?? 0 }} trong tổng số {{ $ads->total() }} quảng cáo
        </p>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="70">Ảnh</th>
                        <th>Tên quảng cáo</th>
                        <th width="110">Dạng hiển thị</th>
                        <th width="150">Trang áp dụng</th>
                        <th width="130">Tần suất</th>
                        <th width="70" class="text-center">Ưu tiên</th>
                        <th width="100" class="text-center">Trạng thái</th>
                        <th width="120" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ads as $ad)
                        @php $previewImage = $ad->image ?: optional($ad->items->first())->image; @endphp
                        <tr>
                            <td class="text-muted">{{ $ad->id }}</td>
                            <td class="text-center">
                                @if($previewImage)
                                    <img src="{{ $previewImage }}" alt="" 
                                         style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #ddd">
                                @else
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $ad->name }}</strong>
                                @if($ad->link)
                                    <br><small class="text-muted">
                                        <i class="fas fa-link"></i> {{ Str::limit($ad->link, 50) }}
                                    </small>
                                @endif
                                @if($ad->items_count)
                                    <br><small class="text-muted"><i class="fas fa-images"></i> {{ $ad->items_count }} items</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ __('messages.ads.modes.'.$ad->display_mode) }}</span>
                                @if($ad->display_mode === 'banner' && $ad->placement)
                                    <br><small class="text-muted">{{ __('messages.ads.placements.'.$ad->placement) }}</small>
                                @endif
                            </td>
                            <td>
                                @forelse(($ad->pages ?? []) as $p)
                                    <span class="badge badge-secondary">{{ __('messages.ads.pages.'.$p) }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                <small>{{ __('messages.ads.frequencies.'.$ad->frequency) }}</small>
                                @if($ad->frequency === 'every_n_views')
                                    <br><small class="text-muted">({{ $ad->frequency_value }} lần)</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $ad->priority }}</span>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.ads.toggle', $ad) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $ad->is_active ? 'btn-success' : 'btn-secondary' }}" 
                                            title="{{ $ad->is_active ? 'Đang bật - Click để tắt' : 'Đang tắt - Click để bật' }}">
                                        <i class="fas fa-{{ $ad->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                        {{ $ad->is_active ? 'Bật' : 'Tắt' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.ads.edit', $ad) }}" 
                                   class="btn btn-sm btn-info" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.ads.destroy', $ad) }}" 
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
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-ad fa-2x mb-2"></i>
                                <p class="mb-0">Chưa có quảng cáo nào. Bấm "Thêm mới" để tạo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $ads->links() }}
        </div>
    </div>
</div>
@endsection
