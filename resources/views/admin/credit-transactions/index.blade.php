@extends('layout.admin')
@section('template_title', __('messages.credit_log.title'))

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('messages.credit_log.title') }}</h3></div>
    <div class="card-body">
        <form method="GET" class="form-row mb-3">
            <div class="col-md-4 mb-2">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Tìm theo user..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3 mb-2">
                <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ __('messages.credit_log.source') }}: tất cả</option>
                    @foreach($types as $val => $_)
                        <option value="{{ $val }}" {{ ($filters['type'] ?? '') === $val ? 'selected' : '' }}>{{ __('messages.credit_log.types.'.$val) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.credit-transactions.index') }}" class="btn btn-sm btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ __('messages.credit_log.user') }}</th>
                        <th>{{ __('messages.credit_log.source') }}</th>
                        <th class="text-right">{{ __('messages.credit_log.change') }}</th>
                        <th class="text-right">{{ __('messages.credit_log.balance') }}</th>
                        <th>{{ __('messages.credit_log.admin') }}</th>
                        <th>{{ __('messages.credit_log.time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $t)
                        <tr>
                            <td class="text-muted">{{ $t->id }}</td>
                            <td>{{ optional($t->user)->username ?? optional($t->user)->name ?? '#'.$t->user_id }}</td>
                            <td>{{ $t->typeLabel() }}@if($t->description)<br><small class="text-muted">{{ $t->description }}</small>@endif</td>
                            <td class="text-right" style="color:{{ $t->amount >= 0 ? '#28a745' : '#dc3545' }};font-weight:600">
                                {{ $t->amount >= 0 ? '+' : '' }}{{ number_format($t->amount) }}
                            </td>
                            <td class="text-right">{{ number_format($t->balance_after) }}</td>
                            <td>{{ optional($t->admin)->username ?? '—' }}</td>
                            <td><small>{{ optional($t->created_at)->format('d/m/Y H:i') }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('messages.credit_log.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
</div>
@endsection
