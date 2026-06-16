@extends('layout.admin')

@section('template_title', 'Sửa VIP #' . $vip->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa fa-edit"></i> Sửa VIP #{{ $vip->id }}
                    @if($vip->user) — <small class="text-muted">{{ $vip->user->username }}</small> @endif
                </h4>
                <a href="{{ route('admin.vips.index') }}" class="btn btn-sm btn-secondary">← Quay lại</a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.vips.update', $vip->id) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Người dùng</label>
                        <input type="text" class="form-control" disabled
                               value="{{ $vip->user ? $vip->user->username . ' (' . $vip->user->email . ')' : '—' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tên gói <span class="text-danger">*</span></label>
                        <input type="text" name="package_name" class="form-control @error('package_name') is-invalid @enderror"
                               value="{{ old('package_name', $vip->package_name) }}" required>
                        @error('package_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Số ngày <span class="text-danger">*</span></label>
                            <input type="number" name="package_days" class="form-control @error('package_days') is-invalid @enderror"
                                   value="{{ old('package_days', $vip->package_days) }}" min="1" required>
                            @error('package_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Xu gói</label>
                            <input type="number" name="package_coins" class="form-control @error('package_coins') is-invalid @enderror"
                                   value="{{ old('package_coins', $vip->package_coins) }}" min="0">
                            @error('package_coins')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Xu/ngày</label>
                            <input type="number" name="daily_credits" class="form-control @error('daily_credits') is-invalid @enderror"
                                   value="{{ old('daily_credits', $vip->daily_credits ?? 0) }}" min="0">
                            @error('daily_credits')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_at" class="form-control @error('start_at') is-invalid @enderror"
                                   value="{{ old('start_at', $vip->start_at?->format('Y-m-d\TH:i')) }}" required>
                            @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hết hạn <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_at" class="form-control @error('end_at') is-invalid @enderror"
                                   value="{{ old('end_at', $vip->end_at?->format('Y-m-d\TH:i')) }}" required>
                            @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Extend shortcut --}}
                    <div class="alert alert-info py-2 d-flex gap-2 align-items-center flex-wrap">
                        <span><i class="fa fa-bolt"></i> Gia hạn nhanh:</span>
                        @foreach([7,14,30,60,90] as $d)
                            <button type="button" class="btn btn-sm btn-outline-primary extend-btn" data-days="{{ $d }}">+{{ $d }}d</button>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Lưu thay đổi</button>
                        <a href="{{ route('admin.vips.show', $vip->id) }}" class="btn btn-info">Chi tiết</a>
                        <a href="{{ route('admin.vips.index') }}" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.extend-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var days = parseInt(this.dataset.days);
        var endInput = document.querySelector('[name="end_at"]');
        var current = endInput.value ? new Date(endInput.value) : new Date();
        current.setDate(current.getDate() + days);
        var pad = n => String(n).padStart(2,'0');
        endInput.value = current.getFullYear()+'-'+pad(current.getMonth()+1)+'-'+pad(current.getDate())
                        +'T'+pad(current.getHours())+':'+pad(current.getMinutes());
    });
});
</script>
@endsection
