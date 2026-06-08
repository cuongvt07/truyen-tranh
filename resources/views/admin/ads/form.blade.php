@extends('layout.admin')

@section('template_title', $ad->exists ? 'Sửa quảng cáo' : 'Thêm quảng cáo')

@section('content')
@php
    $isEdit = $ad->exists;
    $selectedPages = old('pages', $ad->pages ?? ['all']);
    $fmt = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d\TH:i') : '';
@endphp
<div class="container">
    <h2 class="my-3">{{ $isEdit ? 'Sửa quảng cáo' : 'Thêm quảng cáo' }}</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('admin.ads.update', $ad) : route('admin.ads.store') }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="card mb-3">
            <div class="card-header"><strong>Thông tin cơ bản</strong></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Tên quảng cáo <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $ad->name) }}" required placeholder="VD: Banner top trang chủ">
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Ảnh quảng cáo (tải lên)</label>
                        <input type="file" name="image_file" id="ad_image_file" class="form-control-file" accept="image/*">
                        <div class="mt-2">
                            <img id="ad_image_preview" src="{{ $ad->image ?? '' }}" alt="" style="max-height:90px;{{ $ad->image ? '' : 'display:none' }};border:1px solid #ddd;border-radius:4px">
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Hoặc URL ảnh ngoài</label>
                        <input type="text" name="image_url" class="form-control" value="{{ old('image_url', $ad->image_url) }}" placeholder="https://...">
                        <small class="form-text text-muted">Nếu điền URL sẽ ưu tiên dùng URL thay cho ảnh tải lên.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Link đích khi click</label>
                    <input type="text" name="link" class="form-control" value="{{ old('link', $ad->link) }}" placeholder="https://...">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Cách hiển thị</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Dạng chạy <span class="text-danger">*</span></label>
                        <select name="display_mode" id="display_mode" class="form-control">
                            @foreach(\App\Models\Ad::MODES as $val => $label)
                                <option value="{{ $val }}" {{ old('display_mode', $ad->display_mode) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6 mode-banner">
                        <label>Vị trí slot (banner)</label>
                        <select name="placement" class="form-control">
                            @foreach(\App\Models\Ad::PLACEMENTS as $val => $label)
                                <option value="{{ $val }}" {{ old('placement', $ad->placement) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Chèn ở các trang</label>
                    <div class="d-flex flex-wrap" style="gap:14px">
                        @foreach(\App\Models\Ad::PAGES as $val => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pages[]" value="{{ $val }}"
                                       id="page_{{ $val }}" {{ in_array($val, $selectedPages) ? 'checked' : '' }}>
                                <label class="form-check-label" for="page_{{ $val }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                    <small class="form-text text-muted">Chọn "Toàn site" để hiện ở mọi trang.</small>
                </div>
            </div>
        </div>

        {{-- Tần suất: cho popup + click_anywhere --}}
        <div class="card mb-3 mode-popup mode-click_anywhere">
            <div class="card-header"><strong>Tần suất lặp lại</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Tần suất</label>
                        <select name="frequency" id="frequency" class="form-control">
                            @foreach(\App\Models\Ad::FREQUENCIES as $val => $label)
                                <option value="{{ $val }}" {{ old('frequency', $ad->frequency) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 freq-n">
                        <label>Mỗi N lượt xem</label>
                        <input type="number" name="frequency_value" class="form-control" min="1" value="{{ old('frequency_value', $ad->frequency_value ?? 1) }}">
                    </div>
                    <div class="form-group col-md-3 mode-popup">
                        <label>Đếm ngược đóng (giây)</label>
                        <input type="number" name="delay_seconds" class="form-control" min="0" value="{{ old('delay_seconds', $ad->delay_seconds ?? 0) }}">
                        <small class="form-text text-muted">0 = cho đóng ngay.</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Sau khi đã click (link đã chạy)</label>
                        <select name="after_click" id="after_click" class="form-control">
                            @foreach(\App\Models\Ad::AFTER_CLICKS as $val => $label)
                                <option value="{{ $val }}" {{ old('after_click', $ad->after_click ?? 'none') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 cooldown-field">
                        <label>Chờ lại (giây)</label>
                        <input type="number" name="cooldown_seconds" class="form-control" min="0" value="{{ old('cooldown_seconds', $ad->cooldown_seconds ?? 0) }}">
                        <small class="form-text text-muted">VD: 300 = 5 phút.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cấu hình riêng đọc chapter --}}
        <div class="card mb-3 mode-chapter">
            <div class="card-header"><strong>Cấu hình khi đọc chapter</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Hiện từ chương số</label>
                        <input type="number" name="chapter_start" class="form-control" min="1" value="{{ old('chapter_start', $ad->chapter_start ?? 2) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Mỗi N chương</label>
                        <input type="number" name="chapter_interval" class="form-control" min="1" value="{{ old('chapter_interval', $ad->chapter_interval ?? 1) }}">
                    </div>
                    <div class="form-group col-md-6 d-flex align-items-end" style="gap:20px">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="hide_for_vip" id="hide_for_vip" value="1" {{ old('hide_for_vip', $ad->hide_for_vip ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="hide_for_vip">Ẩn với user VIP</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="require_click" id="require_click" value="1" {{ old('require_click', $ad->require_click ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="require_click">Phải click mới đọc tiếp</label>
                        </div>
                    </div>
                </div>
                <small class="form-text text-muted">Điều kiện không hiển thị: user VIP (nếu bật), hoặc chương nhỏ hơn "Hiện từ chương số".</small>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Trạng thái</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Ưu tiên</label>
                        <input type="number" name="priority" class="form-control" min="0" value="{{ old('priority', $ad->priority ?? 0) }}">
                        <small class="form-text text-muted">Số nhỏ hiện trước.</small>
                    </div>
                    <div class="form-group col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $ad->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Đang bật</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Cập nhật' : 'Tạo quảng cáo' }}</button>
            <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

@section('AdScripts')
@endsection
<script>
    (function () {
        var modeSelect = document.getElementById('display_mode');
        var freqSelect = document.getElementById('frequency');
        var afterClickSelect = document.getElementById('after_click');

        function toggleByMode() {
            var mode = modeSelect.value;
            ['banner', 'click_anywhere', 'popup', 'chapter'].forEach(function (m) {
                document.querySelectorAll('.mode-' + m).forEach(function (el) {
                    el.style.display = (mode === m) ? '' : 'none';
                });
            });
        }
        function toggleFreq() {
            var show = freqSelect.value === 'every_n_views';
            document.querySelectorAll('.freq-n').forEach(function (el) {
                el.style.display = show ? '' : 'none';
            });
        }
        function toggleCooldown() {
            var show = afterClickSelect.value === 'cooldown';
            document.querySelectorAll('.cooldown-field').forEach(function (el) {
                el.style.display = show ? '' : 'none';
            });
        }
        modeSelect.addEventListener('change', toggleByMode);
        freqSelect.addEventListener('change', toggleFreq);
        afterClickSelect.addEventListener('change', toggleCooldown);
        toggleByMode();
        toggleFreq();
        toggleCooldown();

        // Preview ảnh upload
        var fileInput = document.getElementById('ad_image_file');
        var preview = document.getElementById('ad_image_preview');
        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (ev) { preview.src = ev.target.result; preview.style.display = ''; };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    })();
</script>
@endsection
