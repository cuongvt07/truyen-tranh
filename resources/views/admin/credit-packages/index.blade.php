@extends('layout.admin')

@section('template_title', 'Credit Packages')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa fa-coins"></i> Credit Packages</h4>
                <a href="{{ route('admin.credit-packages.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add package
                </a>
            </div>
            <div class="card-body">
                @if ($message = session('success'))
                    <div class="alert alert-success">{{ $message }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Credits</th>
                                <th>Price USD (PayPal)</th>
                                <th>Price VND (SePay)</th>
                                <th>Featured</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($packages as $pkg)
                                <tr>
                                    <td>{{ $pkg->id }}</td>
                                    <td>
                                        @if($pkg->icon)
                                            <img src="{{ asset($pkg->icon) }}" alt="{{ $pkg->name }}" width="48" height="48" style="object-fit:cover;border-radius:6px;">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $pkg->name }}</strong></td>
                                    <td><i class="fa fa-coins" style="color:#f0c040"></i> {{ number_format($pkg->coins) }}</td>
                                    <td><strong>${{ number_format($pkg->price_usd, 2) }}</strong></td>
                                    <td>{{ number_format($pkg->price_vnd, 0, ',', '.') }}đ</td>
                                    <td>
                                        @if($pkg->is_featured)
                                            <span class="badge badge-warning"><i class="fa fa-star"></i> Featured</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $pkg->sort_order }}</td>
                                    <td>
                                        @if($pkg->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Hidden</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.credit-packages.edit', $pkg->id) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.credit-packages.destroy', $pkg->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this package?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">No packages yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $packages->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
