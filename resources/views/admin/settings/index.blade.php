@extends('layout.admin')

@section('template_title')
{{ __('Settings') }}
@endsection

@section('content')
<div class="container">
    <h2>Cấu hình hệ thống</h2>
    @if(session('success'))
    <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('success-alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 3000);
            }
        });
    </script>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf

        <button class="btn btn-primary mb-3 float-end">Lưu thay đổi</button>

        <!-- KHỐI CẤU HÌNH HỆ THỐNG -->
        <div class="card mb-4 clear-fix">
            <div class="card-header bg-primary text-white">
                🛠 CẤU HÌNH HỆ THỐNG
            </div>
            <div class="card-body">
                <div class="form-group mb-2">
                    <label>Tên website</label>
                    <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? '' }}">
                </div>
                <div class="form-group mb-2">
                    <label>Email quản trị</label>
                    <input type="email" name="admin_email" class="form-control" value="{{ $settings['admin_email'] ?? '' }}">
                </div>
                <div class="form-group mb-2">
                    <label>Logo URL</label>
                    <input type="text" name="logo_url" class="form-control" value="{{ $settings['logo_url'] ?? '' }}">
                </div>
            </div>
        </div>

        <!-- 2 KHỐI NGÂN HÀNG NẰM NGANG -->
        <div class="row">
            <!-- Ngân hàng 1 -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        💰 NGÂN HÀNG 1
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>Tên chủ tài khoản</label>
                            <input type="text" name="bank1_account_name" class="form-control" value="{{ $settings['bank1_account_name'] ?? '' }}">
                        </div>
                        <div class="form-group mb-2">
                            <label>Số tài khoản</label>
                            <input type="text" name="bank1_account_number" class="form-control" value="{{ $settings['bank1_account_number'] ?? '' }}">
                        </div>
                        <div class="form-group mb-2">
                            <label>Ngân hàng</label>
                            <input type="text" name="bank1_name" class="form-control" value="{{ $settings['bank1_name'] ?? '' }}">
                        </div>
                        <div class="form-group mb-2">
                            <label>Ảnh QR/Logo</label>
                            <input type="file" name="bank1_qr_image" class="form-control-file" id="bank1_qr_input">
                            <div class="mt-2">
                                @if(!empty($settings['bank1_qr_image']))
                                <img id="bank1_qr_preview" src="{{ asset('storage/'.$settings['bank1_qr_image']) }}" alt="QR1" style="max-width: 100%; border:1px solid #ccc;">
                                @else
                                <img id="bank1_qr_preview" src="" alt="QR1" style="max-width: 100%; border:1px solid #ccc;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ngân hàng 2 -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        💰 NGÂN HÀNG 2
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label>Tên chủ tài khoản</label>
                            <input type="text" name="bank2_account_name" class="form-control" value="{{ $settings['bank2_account_name'] ?? '' }}">
                        </div>
                        <div class="form-group mb-2">
                            <label>Số tài khoản</label>
                            <input type="text" name="bank2_account_number" class="form-control" value="{{ $settings['bank2_account_number'] ?? '' }}">
                        </div>
                        <div class="form-group mb-2">
                            <label>Ngân hàng</label>
                            <input type="text" name="bank2_name" class="form-control" value="{{ $settings['bank2_name'] ?? '' }}">
                        </div>
                        <div class="form-group mb-2">
                            <label>Ảnh QR/Logo</label>
                            <input type="file" name="bank2_qr_image" class="form-control-file" id="bank2_qr_input">
                            <div class="mt-2">
                                @if(!empty($settings['bank2_qr_image']))
                                <img id="bank2_qr_preview" src="{{ asset('storage/'.$settings['bank2_qr_image']) }}" alt="QR2" style="max-width: 100%; border:1px solid #ccc;">
                                @else
                                <img id="bank2_qr_preview" src="" alt="QR2" style="max-width: 100%; border:1px solid #ccc;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KHỐI GÓI ƯU ĐÃI PREMIUM -->
        @php
            $packages = getPremiumPackages();
        @endphp

        <div class="card mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <span>🎁 CẤU HÌNH GÓI ƯU ĐÃI PREMIUM</span>
                <button type="button" class="btn btn-sm btn-light ms-auto" style="position: relative;
                    left: 35%;" id="add-package-btn">+ Thêm gói</button>
            </div>
            <div class="card-body">
                <div class="row" id="package-container">
                    @if(!empty($packages))
                    @foreach($packages as $i => $package)
                    <div class="col-md-4 mb-4 package-item">
                        <div class="border p-3 position-relative">
                            <button type="button" class="btn btn-sm btn-danger position-absolute" style="top:5px;right:5px;" onclick="this.closest('.package-item').remove()">Xóa</button>
                            <div class="form-group mb-2">
                                <label>Tên gói {{ $i }}</label>
                                <input type="text" name="premium_package_{{ $i }}_name" class="form-control" value="{{ $package['name'] }}">
                            </div>
                            <div class="form-group mb-2">
                                <label>Số xu gói {{ $i }}</label>
                                <input type="number" name="premium_package_{{ $i }}_coins" class="form-control" value="{{ $package['coins'] }}">
                            </div>
                            <div class="form-group mb-2">
                                <label>Số ngày VIP gói {{ $i }}</label>
                                <input type="number" name="premium_package_{{ $i }}_days" class="form-control" value="{{ $package['days'] }}">
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview QR Bank 1
        const bank1Input = document.getElementById('bank1_qr_input');
        const bank1Preview = document.getElementById('bank1_qr_preview');
        if (bank1Input) {
            bank1Input.addEventListener('change', function(event) {
                const [file] = event.target.files;
                if (file) {
                    bank1Preview.src = URL.createObjectURL(file);
                }
            });
        }
        // Preview QR Bank 2
        const bank2Input = document.getElementById('bank2_qr_input');
        const bank2Preview = document.getElementById('bank2_qr_preview');
        if (bank2Input) {
            bank2Input.addEventListener('change', function(event) {
                const [file] = event.target.files;
                if (file) {
                    bank2Preview.src = URL.createObjectURL(file);
                }
            });
        }
        // Thêm gói mới
        const container = document.getElementById('package-container');
        const addBtn = document.getElementById('add-package-btn');
        let count = container.querySelectorAll('.package-item').length;
        addBtn.addEventListener('click', function() {
            count++;
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-4 package-item';
            col.innerHTML = `
                <div class="border p-3 position-relative">
                    <button type="button" class="btn btn-sm btn-danger position-absolute" style="top:5px;right:5px;" onclick="this.closest('.package-item').remove()">Xóa</button>
                    <div class="form-group mb-2">
                        <label>Tên gói ${count}</label>
                        <input type="text" name="premium_package_${count}_name" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>Số xu gói ${count}</label>
                        <input type="number" name="premium_package_${count}_coins" class="form-control">
                    </div>
                    <div class="form-group mb-2">
                        <label>Số ngày VIP gói ${count}</label>
                        <input type="number" name="premium_package_${count}_days" class="form-control">
                    </div>
                </div>`;
            container.appendChild(col);
        });
    });
</script>