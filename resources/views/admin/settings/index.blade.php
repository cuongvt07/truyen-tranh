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
                <div class="form-group mb-3">
                    <x-admin.image-upload name="logo_file" label="Tải ảnh logo"
                        :current="!empty($settings['logo_file']) ? asset('storage/'.$settings['logo_file']) : null" />
                </div>
                <div class="form-group mb-2">
                    <x-admin.image-upload name="favicon_file" label="Favicon (32×32 hoặc .ico)"
                        accept="image/x-icon,image/png,image/svg+xml" :height="40"
                        :current="!empty($settings['favicon_file']) ? asset('storage/'.$settings['favicon_file']) : null" />
                </div>
            </div>
        </div>

        <!-- KHỐI CẤU HÌNH FOOTER CHƯƠNG -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                📝 CẤU HÌNH FOOTER CHƯƠNG (hiển thị ở cuối nội dung mọi chương)
            </div>
            <div class="card-body row">
                <div class="form-group col-md-6 mb-2">
                    <label>Dòng chữ 1 <small class="text-muted">(vd: Chapter written by)</small></label>
                    <input type="text" name="chapter_footer_text1" class="form-control" value="{{ $settings['chapter_footer_text1'] ?? '' }}">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label>Dòng chữ 2 <small class="text-muted">(vd: tên nhóm dịch)</small></label>
                    <input type="text" name="chapter_footer_text2" class="form-control" value="{{ $settings['chapter_footer_text2'] ?? '' }}">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <label>Link <small class="text-muted">(click vào chữ/ảnh sẽ mở link này)</small></label>
                    <input type="url" name="chapter_footer_link" class="form-control" placeholder="https://..." value="{{ $settings['chapter_footer_link'] ?? '' }}">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <x-admin.image-upload name="chapter_footer_image" label="Ảnh" :height="64"
                        :current="!empty($settings['chapter_footer_image']) ? asset('storage/'.$settings['chapter_footer_image']) : null" />
                </div>
            </div>
        </div>

        <!-- KHỐI NGÂN HÀNG -->
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
                            <x-admin.image-upload name="bank1_qr_image" label="Ảnh QR/Logo" :height="120"
                                :current="!empty($settings['bank1_qr_image']) ? asset('storage/'.$settings['bank1_qr_image']) : null" />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- PAGE CONTENT SETTINGS -->
        <div class="card mb-4 d-none">
            <div class="card-header bg-secondary text-white">
                Cấu hình nội dung trang Forum / FAQ / Rules
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Có thể nhập HTML. Nếu để trống, website sẽ dùng nội dung clone mặc định từ Novelight.
                </p>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3">English</h5>
                        <div class="form-group mb-3">
                            <label>Forum title</label>
                            <input type="text" name="page_forum_title_en" class="form-control" value="{{ $settings['page_forum_title_en'] ?? '' }}" placeholder="Forum">
                        </div>
                        <div class="form-group mb-4">
                            <label>Forum content HTML</label>
                            <textarea name="page_forum_content_en" class="form-control" rows="8" placeholder="Leave empty to use default content">{{ $settings['page_forum_content_en'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>FAQ title</label>
                            <input type="text" name="page_faq_title_en" class="form-control" value="{{ $settings['page_faq_title_en'] ?? '' }}" placeholder="Answers to frequently asked questions and problems">
                        </div>
                        <div class="form-group mb-4">
                            <label>FAQ index content HTML</label>
                            <textarea name="page_faq_content_en" class="form-control" rows="6" placeholder="Leave empty to use default content">{{ $settings['page_faq_content_en'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>FAQ Account title</label>
                            <input type="text" name="page_faq_account_title_en" class="form-control" value="{{ $settings['page_faq_account_title_en'] ?? '' }}" placeholder="Account">
                        </div>
                        <div class="form-group mb-4">
                            <label>FAQ Account content HTML</label>
                            <textarea name="page_faq_account_content_en" class="form-control" rows="6" placeholder="Leave empty to use default content">{{ $settings['page_faq_account_content_en'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>FAQ General title</label>
                            <input type="text" name="page_faq_general_title_en" class="form-control" value="{{ $settings['page_faq_general_title_en'] ?? '' }}" placeholder="General">
                        </div>
                        <div class="form-group mb-4">
                            <label>FAQ General content HTML</label>
                            <textarea name="page_faq_general_content_en" class="form-control" rows="6" placeholder="Leave empty to use default content">{{ $settings['page_faq_general_content_en'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>Rules title</label>
                            <input type="text" name="page_rules_title_en" class="form-control" value="{{ $settings['page_rules_title_en'] ?? '' }}" placeholder="General Site Rules and Ban Reasons">
                        </div>
                        <div class="form-group mb-2">
                            <label>Rules content HTML</label>
                            <textarea name="page_rules_content_en" class="form-control" rows="10" placeholder="Leave empty to use default content">{{ $settings['page_rules_content_en'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3">Tiếng Việt</h5>
                        <div class="form-group mb-3">
                            <label>Tiêu đề Forum</label>
                            <input type="text" name="page_forum_title_vi" class="form-control" value="{{ $settings['page_forum_title_vi'] ?? '' }}" placeholder="Diễn đàn">
                        </div>
                        <div class="form-group mb-4">
                            <label>Nội dung Forum HTML</label>
                            <textarea name="page_forum_content_vi" class="form-control" rows="8" placeholder="Để trống để dùng nội dung mặc định">{{ $settings['page_forum_content_vi'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tiêu đề FAQ</label>
                            <input type="text" name="page_faq_title_vi" class="form-control" value="{{ $settings['page_faq_title_vi'] ?? '' }}" placeholder="Câu hỏi thường gặp và các vấn đề phổ biến">
                        </div>
                        <div class="form-group mb-4">
                            <label>Nội dung FAQ index HTML</label>
                            <textarea name="page_faq_content_vi" class="form-control" rows="6" placeholder="Để trống để dùng nội dung mặc định">{{ $settings['page_faq_content_vi'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tiêu đề FAQ Tài khoản</label>
                            <input type="text" name="page_faq_account_title_vi" class="form-control" value="{{ $settings['page_faq_account_title_vi'] ?? '' }}" placeholder="Tài khoản">
                        </div>
                        <div class="form-group mb-4">
                            <label>Nội dung FAQ Tài khoản HTML</label>
                            <textarea name="page_faq_account_content_vi" class="form-control" rows="6" placeholder="Để trống để dùng nội dung mặc định">{{ $settings['page_faq_account_content_vi'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tiêu đề FAQ Chung</label>
                            <input type="text" name="page_faq_general_title_vi" class="form-control" value="{{ $settings['page_faq_general_title_vi'] ?? '' }}" placeholder="Chung">
                        </div>
                        <div class="form-group mb-4">
                            <label>Nội dung FAQ Chung HTML</label>
                            <textarea name="page_faq_general_content_vi" class="form-control" rows="6" placeholder="Để trống để dùng nội dung mặc định">{{ $settings['page_faq_general_content_vi'] ?? '' }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tiêu đề Rules</label>
                            <input type="text" name="page_rules_title_vi" class="form-control" value="{{ $settings['page_rules_title_vi'] ?? '' }}" placeholder="Nội quy chung và lý do khóa tài khoản">
                        </div>
                        <div class="form-group mb-2">
                            <label>Nội dung Rules HTML</label>
                            <textarea name="page_rules_content_vi" class="form-control" rows="10" placeholder="Để trống để dùng nội dung mặc định">{{ $settings['page_rules_content_vi'] ?? '' }}</textarea>
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
