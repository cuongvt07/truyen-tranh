@extends('layout.admin')

@section('template_title', 'Chi tiết VIP #' . $vip->id)

@section('content')
@php $isActive = $vip->end_at && $vip->end_at->isFuture(); @endphp

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="fa fa-crown" style="color:#f0c040"></i>
        VIP #{{ $vip->id }}
        @if($isActive)
            <span class="badge badge-success ml-2">Active — còn {{ now()->diffInDays($vip->end_at) }} ngày</span>
        @else
            <span class="badge badge-secondary ml-2">Hết hạn</span>
        @endif
    </h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.vips.edit', $vip->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Sửa / Gia hạn</a>
        <a href="{{ route('admin.vips.index') }}" class="btn btn-secondary btn-sm">← Danh sách</a>
    </div>
</div>

<div class="row">

    {{-- CỘT TRÁI: User + Gói đăng ký + Giao dịch liên quan --}}
    <div class="col-md-5">

        {{-- Thông tin người dùng --}}
        @if($vip->user)
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fa fa-user"></i> Người dùng</strong></div>
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($vip->user->avatar)
                        <img src="{{ asset($vip->user->avatar) }}" class="rounded-circle" width="54" height="54" style="object-fit:cover;flex-shrink:0">
                    @else
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:54px;height:54px;flex-shrink:0">
                            <i class="fa fa-user text-white fa-lg"></i>
                        </div>
                    @endif
                    <div>
                        <div class="font-weight-bold">{{ $vip->user->username }}</div>
                        <div class="text-muted small">{{ $vip->user->email }}</div>
                        <div class="mt-1">
                            <span class="badge badge-primary">{{ $vip->user->role }}</span>
                            @if($isActive)<span class="badge badge-success ml-1">VIP</span>@endif
                        </div>
                    </div>
                </div>
                <table class="table table-sm mb-2">
                    <tr><th width="130">Họ tên</th><td>{{ $vip->user->name ?? '—' }}</td></tr>
                    <tr><th>Số dư xu</th><td><i class="fa fa-coins" style="color:#f0c040"></i> <strong>{{ number_format($vip->user->points ?? 0) }}</strong></td></tr>
                    <tr><th>Ngày đăng ký</th><td>{{ $vip->user->created_at?->format('d/m/Y') ?? '—' }}</td></tr>
                </table>
                <a href="{{ route('admin.users.show', $vip->user_id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-external-link-alt"></i> Xem hồ sơ đầy đủ
                </a>
            </div>
        </div>
        @endif

        {{-- Thông tin gói đăng ký VIP này --}}
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fa fa-crown"></i> Gói VIP này</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><th width="130" class="pl-3">Tên gói</th><td>{{ $vip->package_name }}</td></tr>
                    <tr><th class="pl-3">Thời hạn</th><td>{{ $vip->package_days }} ngày</td></tr>
                    <tr><th class="pl-3">Xu gói</th><td><i class="fa fa-coins" style="color:#f0c040"></i> {{ number_format($vip->package_coins) }}</td></tr>
                    <tr><th class="pl-3">Xu/ngày</th><td>{{ $vip->daily_credits ? number_format($vip->daily_credits).' xu/ngày' : '—' }}</td></tr>
                    <tr><th class="pl-3">Nhận xu ngày cuối</th><td>{{ $vip->last_daily_credit_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    <tr><th class="pl-3">Bắt đầu</th><td>{{ $vip->start_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    <tr><th class="pl-3">Hết hạn</th><td>
                        {{ $vip->end_at?->format('d/m/Y H:i') ?? '—' }}
                        @if($isActive) <small class="text-success">(còn {{ now()->diffInDays($vip->end_at) }} ngày)</small> @endif
                    </td></tr>
                    <tr><th class="pl-3">Cấp lúc</th><td>{{ $vip->created_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Thông tin gói cấu hình (credit_packages) --}}
        @if($package)
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fa fa-box-open"></i> Cấu hình gói "{{ $package->name }}"</strong></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><th width="130" class="pl-3">Giá VNĐ</th><td>{{ $package->price_vnd ? number_format($package->price_vnd).'đ' : '—' }}</td></tr>
                    <tr><th class="pl-3">Giá USD</th><td>{{ $package->price_usd ? '$'.number_format($package->price_usd,2) : '—' }}</td></tr>
                    <tr><th class="pl-3">Hiển thị giá</th><td>{{ $package->price_display ?? '—' }}</td></tr>
                    <tr><th class="pl-3">Số ngày</th><td>{{ $package->subscription_days }} ngày</td></tr>
                    <tr><th class="pl-3">Xu tặng</th><td>{{ number_format($package->coins) }} xu</td></tr>
                    <tr><th class="pl-3">Xu/ngày</th><td>{{ $package->daily_credits ? number_format($package->daily_credits).' xu/ngày' : '—' }}</td></tr>
                    <tr><th class="pl-3">Trạng thái</th><td>
                        @if($package->is_active) <span class="badge badge-success">Đang bán</span>
                        @else <span class="badge badge-secondary">Đã tắt</span> @endif
                    </td></tr>
                </table>
                <div class="p-2">
                    <a href="{{ route('admin.credit-packages.edit', $package->id) }}" class="btn btn-xs btn-outline-secondary">
                        <i class="fa fa-edit"></i> Sửa gói
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Giao dịch thanh toán liên quan --}}
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="fa fa-receipt"></i> Giao dịch thanh toán liên quan</strong></div>
            <div class="card-body">
                @if($relatedDeposit)
                <table class="table table-sm mb-0">
                    <tr><th width="130">Mã GD</th><td><code>{{ $relatedDeposit->transaction_id ?? '—' }}</code></td></tr>
                    <tr><th>Số tiền</th><td><strong>{{ number_format($relatedDeposit->amount) }}{{ str_contains($relatedDeposit->payment_method,'paypal') ? ' USD' : 'đ' }}</strong></td></tr>
                    <tr><th>Phương thức</th><td>{{ strtoupper($relatedDeposit->payment_method) }}</td></tr>
                    <tr><th>Trạng thái</th><td>
                        @if($relatedDeposit->status==='completed') <span class="badge badge-success">Hoàn thành</span>
                        @elseif($relatedDeposit->status==='pending') <span class="badge badge-warning">Chờ xử lý</span>
                        @else <span class="badge badge-danger">Thất bại</span>
                        @endif
                    </td></tr>
                    <tr><th>Nội dung</th><td><small class="text-muted">{{ $relatedDeposit->content }}</small></td></tr>
                    <tr><th>Ngày thanh toán</th><td>{{ $relatedDeposit->created_at?->format('d/m/Y H:i') }}</td></tr>
                </table>
                @else
                    <p class="text-muted mb-0"><i class="fa fa-info-circle"></i> Không tìm thấy giao dịch khớp (có thể cấp thủ công).</p>
                @endif
            </div>
        </div>

    </div>

    {{-- CỘT PHẢI: Lịch sử VIP + Lịch sử nạp tiền --}}
    <div class="col-md-7">

        {{-- Lịch sử VIP của user --}}
        <div class="card mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <strong><i class="fa fa-history"></i> Lịch sử VIP của user</strong>
                <span class="badge badge-info">{{ $allVips->count() }} bản ghi</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr><th>#</th><th>Gói</th><th>Xu/ngày</th><th>Bắt đầu</th><th>Hết hạn</th><th>Trạng thái</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($allVips as $v)
                        @php $a = $v->end_at && $v->end_at->isFuture(); @endphp
                        <tr class="{{ $v->id == $vip->id ? 'table-warning font-weight-bold' : ($a ? '' : 'text-muted') }}">
                            <td>{{ $v->id }}</td>
                            <td>
                                {{ $v->package_name }}
                                <br><small>{{ $v->package_days }}d · <i class="fa fa-coins" style="color:#f0c040"></i>{{ number_format($v->package_coins) }}</small>
                            </td>
                            <td>{{ $v->daily_credits ? number_format($v->daily_credits) : '—' }}</td>
                            <td><small>{{ $v->start_at?->format('d/m/Y') }}</small></td>
                            <td><small>{{ $v->end_at?->format('d/m/Y') }}</small></td>
                            <td>
                                @if($a) <span class="badge badge-success">Active</span>
                                @else <span class="badge badge-secondary">Expired</span>
                                @endif
                            </td>
                            <td>
                                @if($v->id != $vip->id)
                                <a href="{{ route('admin.vips.show', $v->id) }}" class="btn btn-xs btn-info">Xem</a>
                                @else <i class="fa fa-arrow-left text-warning"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Lịch sử nạp tiền toàn bộ --}}
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <strong><i class="fa fa-money-bill-wave"></i> Lịch sử nạp / mua</strong>
                <span class="badge badge-info">{{ $deposits->count() }} giao dịch</span>
            </div>
            <div class="card-body p-0">
                @if($deposits->isEmpty())
                    <p class="text-muted p-3 mb-0">Chưa có giao dịch nào.</p>
                @else
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr><th>Mã GD</th><th>Số tiền</th><th>PT thanh toán</th><th>Nội dung</th><th>Trạng thái</th><th>Ngày</th></tr>
                    </thead>
                    <tbody>
                        @foreach($deposits as $dep)
                        <tr class="{{ $relatedDeposit && $dep->id == $relatedDeposit->id ? 'table-warning' : '' }}">
                            <td><small><code>{{ Str::limit($dep->transaction_id ?? '—', 16) }}</code></small></td>
                            <td class="text-nowrap font-weight-bold">
                                {{ number_format($dep->amount) }}{{ str_contains($dep->payment_method,'paypal') ? ' USD' : 'đ' }}
                            </td>
                            <td><small>{{ strtoupper($dep->payment_method) }}</small></td>
                            <td style="max-width:200px"><small class="text-muted">{{ Str::limit($dep->content, 60) }}</small></td>
                            <td>
                                @if($dep->status==='completed') <span class="badge badge-success">OK</span>
                                @elseif($dep->status==='pending') <span class="badge badge-warning">Chờ</span>
                                @else <span class="badge badge-danger">Fail</span>
                                @endif
                            </td>
                            <td class="text-nowrap"><small>{{ $dep->created_at?->format('d/m/Y H:i') }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
