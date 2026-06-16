@extends('layout.admin')

@section('template_title', 'VIP Accounts')

@section('content')
<div class="row mb-3">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['total']) }}</h3><p>Tổng bản ghi VIP</p></div>
            <div class="icon"><i class="fa fa-crown"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['active']) }}</h3><p>Đang hoạt động</p></div>
            <div class="icon"><i class="fa fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['expired']) }}</h3><p>Đã hết hạn</p></div>
            <div class="icon"><i class="fa fa-calendar-times"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="search" name="search" class="form-control form-control-sm" style="width:200px"
                           placeholder="Username / email / tên" value="{{ request('search') }}">
                    <select name="status" class="form-select form-select-sm" style="width:130px">
                        <option value="">Tất cả</option>
                        <option value="active"  {{ request('status')=='active'  ? 'selected':'' }}>Đang active</option>
                        <option value="expired" {{ request('status')=='expired' ? 'selected':'' }}>Hết hạn</option>
                    </select>
                    <select name="package" class="form-select form-select-sm" style="width:180px">
                        <option value="">Tất cả gói</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg }}" {{ request('package')==$pkg ? 'selected':'' }}>{{ $pkg }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-search"></i> Lọc</button>
                    <a href="{{ route('admin.vips.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                </form>
                <a href="{{ route('admin.vips.create') }}" class="btn btn-sm btn-success">
                    <i class="fa fa-crown"></i> Cấp VIP thủ công
                </a>
            </div>

            <div class="card-body p-0">
                @if($message = session('success'))
                    <div class="alert alert-success m-3">{{ $message }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Người dùng</th>
                                <th>Gói</th>
                                <th>Ngày</th>
                                <th>Xu gói</th>
                                <th>Xu/ngày</th>
                                <th>Bắt đầu</th>
                                <th>Hết hạn</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vips as $vip)
                                @php $isActive = $vip->end_at && $vip->end_at->isFuture(); @endphp
                                <tr class="{{ $isActive ? '' : 'table-secondary text-muted' }}">
                                    <td>{{ $vip->id }}</td>
                                    <td>
                                        @if($vip->user)
                                            <a href="{{ route('admin.vips.show', $vip->id) }}" class="fw-semibold">{{ $vip->user->username }}</a>
                                            <br><small class="text-muted">{{ $vip->user->email }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $vip->package_name }}</td>
                                    <td>{{ $vip->package_days }}d</td>
                                    <td><i class="fa fa-coins" style="color:#f0c040"></i> {{ number_format($vip->package_coins) }}</td>
                                    <td>{{ $vip->daily_credits ? number_format($vip->daily_credits).'/ngày' : '—' }}</td>
                                    <td>{{ $vip->start_at ? $vip->start_at->format('d/m/Y') : '—' }}</td>
                                    <td>{{ $vip->end_at ? $vip->end_at->format('d/m/Y H:i') : '—' }}</td>
                                    <td>
                                        @if($isActive)
                                            <span class="badge badge-success">{{ now()->diffInDays($vip->end_at) }}d còn lại</span>
                                        @else
                                            <span class="badge badge-secondary">Hết hạn</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.vips.show', $vip->id) }}" class="btn btn-sm btn-info" title="Chi tiết"><i class="fa fa-eye"></i></a>
                                        <a href="{{ route('admin.vips.edit', $vip->id) }}" class="btn btn-sm btn-warning" title="Sửa"><i class="fa fa-edit"></i></a>
                                        <form action="{{ route('admin.vips.destroy', $vip->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Thu hồi VIP này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Thu hồi"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-3">Không có bản ghi VIP nào.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $vips->links() }}</div>
        </div>
    </div>
</div>
@endsection
