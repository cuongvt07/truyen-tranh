@extends('layout.admin')

@section('template_title', 'Grant VIP manually')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa fa-crown"></i> Grant VIP manually</h4>
                <a href="{{ route('admin.vips.index') }}" class="btn btn-sm btn-secondary">← Back</a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.vips.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Select user --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected':'' }}>
                                    {{ $u->username }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Package name <span class="text-danger">*</span></label>
                        <input type="text" name="package_name" class="form-control @error('package_name') is-invalid @enderror"
                               value="{{ old('package_name', 'Premium Membership') }}" required>
                        @error('package_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Days <span class="text-danger">*</span></label>
                            <input type="number" name="package_days" class="form-control @error('package_days') is-invalid @enderror"
                                   value="{{ old('package_days', 30) }}" min="1" required>
                            @error('package_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Credits used (record only)</label>
                            <input type="number" name="package_coins" class="form-control @error('package_coins') is-invalid @enderror"
                                   value="{{ old('package_coins', 0) }}" min="0">
                            @error('package_coins')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start date <span class="text-danger">*</span></label>
                            <input type="date" name="start_at" class="form-control @error('start_at') is-invalid @enderror"
                                   value="{{ old('start_at', date('Y-m-d')) }}" required>
                            @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="alert alert-info py-2">
                        <i class="fa fa-info-circle"></i>
                        End date = Start date + Days. Calculated automatically.
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-crown"></i> Grant VIP
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
