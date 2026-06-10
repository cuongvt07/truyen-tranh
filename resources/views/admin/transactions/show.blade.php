@extends('layout.admin')

@section('template_title', 'Transaction #' . $transaction->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">

        @if($message = session('success'))
            <div class="alert alert-success">{{ $message }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa fa-receipt"></i> Transaction #{{ $transaction->id }}</h4>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-secondary">← Back</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th style="width:200px">Transaction ID</th><td><code>{{ $transaction->transaction_id }}</code></td></tr>
                    <tr>
                        <th>User</th>
                        <td>
                            @if($transaction->user)
                                <a href="{{ route('admin.users.show', $transaction->user_id) }}">
                                    {{ $transaction->user->username }} ({{ $transaction->user->email }})
                                </a>
                            @else
                                <span class="text-muted">Unknown (user_id: {{ $transaction->user_id }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td>
                            @if($transaction->payment_method === 'paypal')
                                <strong>${{ number_format($transaction->amount, 2) }}</strong>
                            @else
                                <strong>{{ number_format($transaction->amount, 0, ',', '.') }}$</strong>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Payment method</th>
                        <td>
                            @if($transaction->payment_method === 'paypal')
                                <span class="badge" style="background:#003087;color:#fff">PayPal</span>
                            @else
                                <span class="badge badge-info">{{ strtoupper($transaction->payment_method) }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th>Payment Reference</th><td>{{ $transaction->payment_reference ?: '—' }}</td></tr>
                    <tr><th>Content</th><td>{{ $transaction->content ?: '—' }}</td></tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($transaction->status === 'completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($transaction->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-danger">Failed</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th>Created at</th><td>{{ $transaction->created_at->format('m/d/Y H:i:s') }}</td></tr>
                    <tr><th>Updated at</th><td>{{ $transaction->updated_at->format('m/d/Y H:i:s') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h5 class="mb-0"><i class="fa fa-edit"></i> Update status manually</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.transactions.update-status', $transaction->id) }}" method="POST" class="d-flex gap-2 align-items-center">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select" style="width:180px">
                        <option value="pending"   {{ $transaction->status == 'pending'   ? 'selected':'' }}>Pending</option>
                        <option value="completed" {{ $transaction->status == 'completed' ? 'selected':'' }}>Completed</option>
                        <option value="failed"    {{ $transaction->status == 'failed'    ? 'selected':'' }}>Failed</option>
                    </select>
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('Update status? Marking as Completed will add credits to the user account.')">
                        <i class="fa fa-save"></i> Save
                    </button>
                </form>
                <small class="text-muted mt-2 d-block">
                    <i class="fa fa-info-circle"></i>
                    Marking as <strong>Completed</strong> will automatically add the corresponding credits to the user's account.
                </small>
            </div>
        </div>

    </div>
</div>
@endsection
