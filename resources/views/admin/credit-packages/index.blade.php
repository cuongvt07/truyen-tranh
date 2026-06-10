@extends('layout.admin')

@section('template_title', 'Gói Credit')

@section('content')
<div class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-coins text-primary"></i> Quản lý Gói Credit
            </h4>
            <a href="{{ route('admin.credit-packages.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm gói Credit
            </a>
        </div>

        {{-- Info Banner --}}
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Credit:</strong> Người dùng mua một lần, nhận credits ngay.
            <strong class="ml-3">Subscription:</strong> Gói đăng ký, ẩn quảng cáo + nhận credits hàng ngày trong thời gian đăng ký.
        </div>

        {{-- Stats --}}
        <div class="mb-2 text-muted small">
            <i class="fas fa-info-circle"></i>
            Tổng: <strong>{{ $packages->total() }}</strong> gói
        </div>

        {{-- Table Card --}}
        <div class="card">
            <div class="card-body p-0">
                @forelse($packages as $pkg)
                    @if($loop->first)
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="60">ID</th>
                                <th width="70" class="text-center">Icon</th>
                                <th>Tên gói</th>
                                <th width="100" class="text-center">Loại</th>
                                <th width="120" class="text-center">Credits</th>
                                <th width="150">Subscription</th>
                                <th width="100" class="text-center">Giá USD</th>
                                <th width="80" class="text-center">Thứ tự</th>
                                <th width="100" class="text-center">Trạng thái</th>
                                <th width="120" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                    @endif
                            <tr>
                                <td class="align-middle">
                                    <span class="badge badge-secondary">{{ $pkg->id }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    @if($pkg->icon)
                                        <img src="{{ asset($pkg->icon) }}" 
                                             alt="{{ $pkg->name }}" 
                                             style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <div class="font-weight-bold">{{ $pkg->name }}</div>
                                    @if($pkg->is_featured)
                                        <span class="badge badge-warning badge-sm">
                                            <i class="fas fa-star"></i> Nổi bật
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    @if($pkg->isSubscription())
                                        <span class="badge badge-primary">
                                            <i class="fas fa-crown"></i> Subscription
                                        </span>
                                    @else
                                        <span class="badge badge-info">
                                            <i class="fas fa-coins"></i> Credit
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-warning badge-pill">
                                        <i class="fas fa-coins"></i> {{ number_format($pkg->coins) }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    @if($pkg->isSubscription())
                                        <div><strong>{{ $pkg->subscription_days }}</strong> ngày</div>
                                        <small class="text-muted">
                                            {{ number_format($pkg->daily_credits) }} credits/ngày
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <strong class="text-success">${{ number_format($pkg->price_usd, 2) }}</strong>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-secondary">{{ $pkg->sort_order }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    @if($pkg->is_active)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-eye-slash"></i> Ẩn
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.credit-packages.edit', $pkg->id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.credit-packages.destroy', $pkg->id) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Bạn có chắc muốn xóa gói «{{ $pkg->name }}»?')">
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
                        <i class="fas fa-coins fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có gói Credit nào</h5>
                        <p class="text-muted mb-3">Tạo gói Credit đầu tiên để người dùng có thể mua</p>
                        <a href="{{ route('admin.credit-packages.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm gói Credit
                        </a>
                    </div>
                @endforelse
            </div>
            
            @if($packages->hasPages())
                <div class="card-footer">
                    {{ $packages->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
