@extends('layout.admin')
@section('template_title', 'Cấu hình thanh toán')

@section('content')
<div class="content"><div class="container-fluid">
    @includeWhen(session('success'), 'admin.partials.flash')

    @php
        $secretField = function ($name, $isSet) {
            return [$name, $isSet];
        };
    @endphp

    <form method="POST" action="{{ route('admin.payment_settings.update') }}">
        @csrf

        <div class="alert alert-warning">
            <i class="fas fa-shield-alt"></i>
            <strong>Bảo mật:</strong> Secret &amp; Webhook ID được <strong>mã hoá</strong> khi lưu và
            <strong>không bao giờ hiển thị lại</strong>. Để trống các ô bí mật nếu muốn <strong>giữ nguyên</strong> giá trị cũ.
            Trang này chỉ <strong>super-admin</strong> mới truy cập được. Nên dùng qua HTTPS.
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fab fa-paypal"></i> PayPal</span>
                <button class="btn btn-light btn-sm">Lưu thay đổi</button>
            </div>
            <div class="card-body">
                {{-- Mode --}}
                <div class="form-group">
                    <label>Chế độ đang dùng</label>
                    <select name="paypal_mode" class="form-control" style="max-width:220px">
                        <option value="sandbox" {{ $mode === 'sandbox' ? 'selected' : '' }}>Sandbox (thử nghiệm)</option>
                        <option value="live" {{ $mode === 'live' ? 'selected' : '' }}>Live (thật)</option>
                    </select>
                    <small class="form-text text-muted">Hệ thống sẽ dùng bộ key tương ứng với chế độ này.</small>
                </div>

                <hr>

                {{-- Sandbox --}}
                <h6 class="text-muted text-uppercase">Sandbox</h6>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Client ID</label>
                        <input type="text" name="paypal_sandbox_client_id" class="form-control" autocomplete="off"
                               value="{{ old('paypal_sandbox_client_id', $sandboxClientId) }}" placeholder="AY...">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Secret
                            @if($flags['sandbox_secret'])<span class="badge badge-success">đã cấu hình</span>@else<span class="badge badge-secondary">chưa có</span>@endif
                        </label>
                        <input type="password" name="paypal_sandbox_secret" class="form-control" autocomplete="new-password"
                               placeholder="{{ $flags['sandbox_secret'] ? '•••••••• (để trống = giữ nguyên)' : 'Nhập secret' }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Webhook ID
                            @if($flags['sandbox_webhook'])<span class="badge badge-success">đã cấu hình</span>@else<span class="badge badge-secondary">chưa có</span>@endif
                        </label>
                        <input type="password" name="paypal_sandbox_webhook_id" class="form-control" autocomplete="new-password"
                               placeholder="{{ $flags['sandbox_webhook'] ? '•••••••• (để trống = giữ nguyên)' : 'Nhập webhook id' }}">
                    </div>
                </div>

                <hr>

                {{-- Live --}}
                <h6 class="text-muted text-uppercase">Live</h6>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Client ID</label>
                        <input type="text" name="paypal_live_client_id" class="form-control" autocomplete="off"
                               value="{{ old('paypal_live_client_id', $liveClientId) }}" placeholder="AY...">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Secret
                            @if($flags['live_secret'])<span class="badge badge-success">đã cấu hình</span>@else<span class="badge badge-secondary">chưa có</span>@endif
                        </label>
                        <input type="password" name="paypal_live_secret" class="form-control" autocomplete="new-password"
                               placeholder="{{ $flags['live_secret'] ? '•••••••• (để trống = giữ nguyên)' : 'Nhập secret' }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Webhook ID
                            @if($flags['live_webhook'])<span class="badge badge-success">đã cấu hình</span>@else<span class="badge badge-secondary">chưa có</span>@endif
                        </label>
                        <input type="password" name="paypal_live_webhook_id" class="form-control" autocomplete="new-password"
                               placeholder="{{ $flags['live_webhook'] ? '•••••••• (để trống = giữ nguyên)' : 'Nhập webhook id' }}">
                    </div>
                </div>

                @if($envFallback['client_id'] || $envFallback['secret'])
                    <div class="alert alert-info mb-0 mt-2">
                        <i class="fas fa-info-circle"></i> Đang có cấu hình PayPal trong <code>.env</code> — sẽ được dùng làm
                        <strong>fallback</strong> khi ô tương ứng ở đây để trống.
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <span><i class="fab fa-google"></i> Đăng nhập Google (OAuth)</span>
                <button class="btn btn-light btn-sm">Lưu thay đổi</button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Redirect URI (khai báo y hệt trong Google Console)</label>
                    <input type="text" class="form-control" readonly value="{{ url('/auth/google/callback') }}">
                    <small class="form-text text-muted">Google Cloud Console → Credentials → OAuth client → Authorized redirect URIs.</small>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Client ID</label>
                        <input type="text" name="google_client_id" class="form-control" autocomplete="off"
                               value="{{ old('google_client_id', $googleClientId) }}" placeholder="....apps.googleusercontent.com">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Client Secret
                            @if($flags['google_secret'])<span class="badge badge-success">đã cấu hình</span>@else<span class="badge badge-secondary">chưa có</span>@endif
                        </label>
                        <input type="password" name="google_client_secret" class="form-control" autocomplete="new-password"
                               placeholder="{{ $flags['google_secret'] ? '•••••••• (để trống = giữ nguyên)' : 'GOCSPX-...' }}">
                    </div>
                </div>
                @if($envFallback['google_client_id'] || $envFallback['google_client_secret'])
                    <div class="alert alert-info mb-0 mt-2">
                        <i class="fas fa-info-circle"></i> Đang có cấu hình Google trong <code>.env</code> — dùng làm
                        <strong>fallback</strong> khi ô tương ứng để trống.
                    </div>
                @endif
            </div>
        </div>

        <button class="btn btn-primary">Lưu thay đổi</button>
    </form>
</div></div>
@endsection
