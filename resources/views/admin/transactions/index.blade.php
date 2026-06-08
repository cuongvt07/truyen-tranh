@extends('layout.admin')

@section('template_title', 'Transactions')

@section('content')
<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ number_format($stats['total']) }}</h3><p>Total transactions</p></div>
            <div class="icon"><i class="fa fa-receipt"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ number_format($stats['completed']) }}</h3><p>Completed</p></div>
            <div class="icon"><i class="fa fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ number_format($stats['pending']) }}</h3><p>Pending</p></div>
            <div class="icon"><i class="fa fa-clock"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-gradient-teal">
            <div class="inner">
                <h3>${{ number_format($stats['revenue'], 2) }}</h3>
                <p>Revenue (completed)</p>
            </div>
            <div class="icon"><i class="fa fa-dollar-sign"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="search" name="search" class="form-control form-control-sm" style="width:220px"
                           placeholder="Search transaction ID / user / content" value="{{ request('search') }}">
                    <select name="status" class="form-select form-select-sm" style="width:130px">
                        <option value="">All statuses</option>
                        <option value="pending"   {{ request('status')=='pending'   ? 'selected':'' }}>Pending</option>
                        <option value="completed" {{ request('status')=='completed' ? 'selected':'' }}>Completed</option>
                        <option value="failed"    {{ request('status')=='failed'    ? 'selected':'' }}>Failed</option>
                    </select>
                    <select name="payment_method" class="form-select form-select-sm" style="width:130px">
                        <option value="">All methods</option>
                        <option value="sepay"  {{ request('payment_method')=='sepay'  ? 'selected':'' }}>SePay</option>
                        <option value="paypal" {{ request('payment_method')=='paypal' ? 'selected':'' }}>PayPal</option>
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-search"></i> Filter</button>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                </form>
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
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Content</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $tx)
                                <tr>
                                    <td>{{ $tx->id }}</td>
                                    <td>
                                        @if($tx->user)
                                            <a href="{{ route('admin.users.show', $tx->user_id) }}">{{ $tx->user->username }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $tx->transaction_id }}</code></td>
                                    <td>
                                        @if($tx->payment_method === 'paypal')
                                            ${{ number_format($tx->amount, 2) }}
                                        @else
                                            {{ number_format($tx->amount, 0, ',', '.') }}đ
                                        @endif
                                    </td>
                                    <td>
                                        @if($tx->payment_method === 'paypal')
                                            <span class="badge" style="background:#003087;color:#fff">PayPal</span>
                                        @else
                                            <span class="badge badge-info">{{ strtoupper($tx->payment_method) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($tx->content, 40) }}</td>
                                    <td>
                                        @if($tx->status === 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($tx->status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td title="{{ $tx->created_at }}">{{ $tx->created_at->format('m/d/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.transactions.show', $tx->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-3">No transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">{{ $transactions->links() }}</div>
        </div>
    </div>
</div>
@endsection
