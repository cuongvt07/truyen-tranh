@extends('layout.admin')

@section('template_title', 'VIP Accounts')

@section('content')
<div class="row mb-3">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['total']) }}</h3><p>Total VIP records</p></div>
            <div class="icon"><i class="fa fa-crown"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['active']) }}</h3><p>Active</p></div>
            <div class="icon"><i class="fa fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ number_format($stats['expired']) }}</h3><p>Expired</p></div>
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
                           placeholder="Search username / email" value="{{ request('search') }}">
                    <select name="status" class="form-select form-select-sm" style="width:140px">
                        <option value="">All</option>
                        <option value="active"  {{ request('status')=='active'  ? 'selected':'' }}>Active</option>
                        <option value="expired" {{ request('status')=='expired' ? 'selected':'' }}>Expired</option>
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-search"></i> Filter</button>
                    <a href="{{ route('admin.vips.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                </form>
                <a href="{{ route('admin.vips.create') }}" class="btn btn-sm btn-success">
                    <i class="fa fa-crown"></i> Grant VIP manually
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
                                <th>User</th>
                                <th>Package</th>
                                <th>Days</th>
                                <th>Credits used</th>
                                <th>Start</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vips as $vip)
                                @php $isActive = $vip->end_at && $vip->end_at->isFuture(); @endphp
                                <tr class="{{ $isActive ? '' : 'table-secondary' }}">
                                    <td>{{ $vip->id }}</td>
                                    <td>
                                        @if($vip->user)
                                            <a href="{{ route('admin.users.show', $vip->user_id) }}">{{ $vip->user->username }}</a>
                                            <br><small class="text-muted">{{ $vip->user->email }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $vip->package_name }}</td>
                                    <td>{{ $vip->package_days }} days</td>
                                    <td><i class="fa fa-coins" style="color:#f0c040"></i> {{ number_format($vip->package_coins) }} credits</td>
                                    <td>{{ $vip->start_at ? $vip->start_at->format('m/d/Y') : '—' }}</td>
                                    <td>{{ $vip->end_at ? $vip->end_at->format('m/d/Y H:i') : '—' }}</td>
                                    <td>
                                        @if($isActive)
                                            <span class="badge badge-success">
                                                {{ now()->diffInDays($vip->end_at) }}d left
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.vips.destroy', $vip->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Revoke this VIP?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i> Revoke
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-3">No VIP records found.</td></tr>
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
