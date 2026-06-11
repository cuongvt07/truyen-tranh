@extends('layout.admin')

@section('template_title', $ad->exists ? 'Chỉnh sửa Quảng cáo: ' . $ad->name : 'Thêm mới Quảng cáo')

@section('content')
@php
    $isEdit = $ad->exists;
    $selectedPages = old('pages', $ad->pages ?? ['all']);
    $itemRows = old('items');
    if ($itemRows === null) {
        $itemRows = $ad->items->map(fn ($item) => [
            'id' => $item->id,
            'title' => $item->title,
            'image_url' => $item->image_url,
            'link' => $item->link,
            'sort_order' => $item->sort_order,
            'is_active' => $item->is_active,
            'image' => $item->image,
        ])->values()->all();
    }
    if (empty($itemRows)) {
        $itemRows = [['title' => '', 'image_url' => '', 'link' => '', 'sort_order' => 0, 'is_active' => true, 'image' => null]];
    }
@endphp

<style>
.mode-chapter .form-group:has(input[name="chapter_start"]),
.mode-chapter .form-group:has(input[name="chapter_interval"]),
.mode-chapter .form-group:has(input[name="chapter_inline_first_after"]),
.mode-chapter .form-group:has(input[name="chapter_inline_every"]) {
    display: none;
}
.mode-chapter:has(input[name="chapter_start"]) > small.form-text {
    display: none;
}
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $isEdit ? 'Chỉnh sửa Quảng cáo' : 'Thêm mới Quảng cáo' }}</h3>
    </div>
    <form method="POST" action="{{ $isEdit ? route('admin.ads.update', $ad) : route('admin.ads.store') }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Thông tin cơ bản --}}
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
            <div class="form-group">
                <label>Tên quảng cáo <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name', $ad->name) }}" required placeholder="VD: Banner top trang chủ">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <x-admin.image-upload name="image_file" label="Ảnh quảng cáo" :height="90"
                    :current="$ad->image ?? null"
                    urlName="image_url" :urlValue="old('image_url', $ad->image_url)"
                    hint="Có thể tải lên hoặc dán URL ảnh ngoài (URL được ưu tiên)." />
            </div>

            <div class="form-group">
                <label>Link đích khi click</label>
                <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" 
                       value="{{ old('link', $ad->link) }}" placeholder="https://...">
                @error('link')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <hr class="my-4">

            {{-- Cách hiển thị --}}
            <h5 class="mb-3"><i class="fas fa-desktop"></i> Cách hiển thị</h5>
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Dạng chạy <span class="text-danger">*</span></label>
                    <select name="display_mode" id="display_mode" class="form-control @error('display_mode') is-invalid @enderror">
                        @foreach(\App\Models\Ad::MODES as $val => $label)
                            <option value="{{ $val }}" {{ old('display_mode', $ad->display_mode) === $val ? 'selected' : '' }}>{{ __('messages.ads.modes.'.$val) }}</option>
                        @endforeach
                    </select>
                    @error('display_mode')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6 mode-banner">
                    <label>Vị trí slot (cho banner)</label>
                    <select name="placement" class="form-control @error('placement') is-invalid @enderror">
                        @foreach(\App\Models\Ad::PLACEMENTS as $val => $label)
                            <option value="{{ $val }}" {{ old('placement', $ad->placement) === $val ? 'selected' : '' }}>{{ __('messages.ads.placements.'.$val) }}</option>
                        @endforeach
                    </select>
                    @error('placement')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>Chèn ở các trang</label>
                <div class="d-flex flex-wrap" style="gap:14px">
                    @foreach(\App\Models\Ad::PAGES as $val => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="pages[]" value="{{ $val }}"
                                   id="page_{{ $val }}" {{ in_array($val, $selectedPages) ? 'checked' : '' }}>
                            <label class="form-check-label" for="page_{{ $val }}">{{ __('messages.ads.pages.'.$val) }}</label>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">Chọn "Toàn site" để hiện ở mọi trang</small>
            </div>

            <hr class="my-4">

            {{-- Tần suất (popup + click_anywhere) --}}
            <div class="mode-popup mode-click_anywhere">
                <h5 class="mb-3"><i class="fas fa-clock"></i> Tần suất lặp lại</h5>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Tần suất hiển thị</label>
                        <select name="frequency" id="frequency" class="form-control @error('frequency') is-invalid @enderror">
                            @foreach(\App\Models\Ad::FREQUENCIES as $val => $label)
                                <option value="{{ $val }}" {{ old('frequency', $ad->frequency) === $val ? 'selected' : '' }}>{{ __('messages.ads.frequencies.'.$val) }}</option>
                            @endforeach
                        </select>
                        @error('frequency')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-3 freq-n">
                        <label>Mỗi N lượt xem</label>
                        <input type="number" name="frequency_value" class="form-control @error('frequency_value') is-invalid @enderror" 
                               min="1" value="{{ old('frequency_value', $ad->frequency_value ?? 1) }}">
                        @error('frequency_value')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-3 mode-popup">
                        <label>Đếm ngược đóng (giây)</label>
                        <input type="number" name="delay_seconds" class="form-control @error('delay_seconds') is-invalid @enderror" 
                               min="0" value="{{ old('delay_seconds', $ad->delay_seconds ?? 0) }}">
                        @error('delay_seconds')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="form-text text-muted">0 = cho đóng ngay</small>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Sau khi đã click</label>
                        <select name="after_click" id="after_click" class="form-control @error('after_click') is-invalid @enderror">
                            @foreach(\App\Models\Ad::AFTER_CLICKS as $val => $label)
                                <option value="{{ $val }}" {{ old('after_click', $ad->after_click ?? 'none') === $val ? 'selected' : '' }}>{{ __('messages.ads.after_clicks.'.$val) }}</option>
                            @endforeach
                        </select>
                        @error('after_click')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="form-text text-muted">{{ __('messages.ads.repeat_hint') }}</small>
                    </div>
                    <div class="form-group col-md-3 cooldown-field">
                        <label>Chờ lại (giây)</label>
                        <input type="number" name="cooldown_seconds" class="form-control @error('cooldown_seconds') is-invalid @enderror" 
                               min="0" value="{{ old('cooldown_seconds', $ad->cooldown_seconds ?? 0) }}">
                        @error('cooldown_seconds')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="form-text text-muted">VD: 300 = 5 phút</small>
                    </div>
                </div>
                <hr class="my-4">
            </div>

            {{-- Cấu hình chapter --}}
            <div class="mode-chapter">
                <h5 class="mb-3"><i class="fas fa-book-open"></i> Cấu hình khi đọc chapter</h5>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label>Hiện từ chương số</label>
                        <input type="number" name="chapter_start" class="form-control @error('chapter_start') is-invalid @enderror" 
                               min="1" value="{{ old('chapter_start', $ad->chapter_start ?? 2) }}">
                        @error('chapter_start')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-3">
                        <label>Mỗi N chương</label>
                        <input type="number" name="chapter_interval" class="form-control @error('chapter_interval') is-invalid @enderror" 
                               min="1" value="{{ old('chapter_interval', $ad->chapter_interval ?? 1) }}">
                        @error('chapter_interval')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-2">
                        <label>Số item chèn trong 1 trang</label>
                        <input type="number" name="chapter_inline_count" class="form-control @error('chapter_inline_count') is-invalid @enderror"
                               min="1" max="20" value="{{ old('chapter_inline_count', $ad->chapter_inline_count ?? 1) }}">
                        @error('chapter_inline_count')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-2">
                        <label>Sau đoạn thứ</label>
                        <input type="number" name="chapter_inline_first_after" class="form-control @error('chapter_inline_first_after') is-invalid @enderror"
                               min="1" max="200" value="{{ old('chapter_inline_first_after', $ad->chapter_inline_first_after ?? 4) }}">
                        @error('chapter_inline_first_after')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-2">
                        <label>Cách mỗi N đoạn</label>
                        <input type="number" name="chapter_inline_every" class="form-control @error('chapter_inline_every') is-invalid @enderror"
                               min="1" max="200" value="{{ old('chapter_inline_every', $ad->chapter_inline_every ?? 8) }}">
                        @error('chapter_inline_every')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group col-md-6 d-flex align-items-end" style="gap:20px">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="hide_for_vip" id="hide_for_vip" 
                                   value="1" {{ old('hide_for_vip', $ad->hide_for_vip ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="hide_for_vip">Ẩn với user VIP</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="require_click" id="require_click" 
                                   value="1" {{ old('require_click', $ad->require_click ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="require_click">Phải click mới đọc tiếp</label>
                        </div>
                    </div>
                </div>
                <small class="form-text text-muted">Điều kiện không hiển thị: user VIP (nếu bật), hoặc chương nhỏ hơn "Hiện từ chương số"</small>
                <hr class="my-4">
            </div>

            {{-- Trạng thái --}}
            <h5 class="mb-3"><i class="fas fa-cog"></i> Cấu hình khác</h5>
            <div class="row">
                <div class="form-group col-md-3">
                    <label>Ưu tiên hiển thị</label>
                    <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror" 
                           min="0" value="{{ old('priority', $ad->priority ?? 0) }}">
                    @error('priority')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="form-text text-muted">Số nhỏ hiện trước</small>
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                               value="1" {{ old('is_active', $ad->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active"><strong>Đang bật quảng cáo</strong></label>
                    </div>
                </div>
            </div>
            <div class="mode-chapter">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-images"></i> Item quảng cáo chapter</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addAdItem">
                        <i class="fas fa-plus"></i> Thêm item
                    </button>
                </div>
                <div id="adItems" class="ad-items">
                    @foreach($itemRows as $index => $item)
                        <div class="ad-item border rounded p-3 mb-3" data-index="{{ $index }}">
                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                            <input type="hidden" class="item-delete" name="items[{{ $index }}][delete]" value="0">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label>Tiêu đề</label>
                                    <input type="text" name="items[{{ $index }}][title]" class="form-control" value="{{ $item['title'] ?? '' }}" placeholder="Find Your Path">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Link</label>
                                    <input type="text" name="items[{{ $index }}][link]" class="form-control" value="{{ $item['link'] ?? '' }}" placeholder="https://...">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Ảnh item</label>
                                    <x-admin.image-upload name="items[{{ $index }}][image_file]"
                                        urlName="items[{{ $index }}][image_url]"
                                        removeName="items[{{ $index }}][image_remove]"
                                        :current="$item['image'] ?? null" :height="52"
                                        :urlValue="$item['image_url'] ?? ''" />
                                </div>
                            </div>
                            <div class="d-flex align-items-center" style="gap:18px">
                                <div class="form-group mb-0" style="width:120px">
                                    <label>Thứ tự</label>
                                    <input type="number" name="items[{{ $index }}][sort_order]" class="form-control" min="0" value="{{ $item['sort_order'] ?? $index }}">
                                </div>
                                <div class="form-check mt-4">
                                    <input type="hidden" name="items[{{ $index }}][is_active]" value="0">
                                    <input class="form-check-input" type="checkbox" name="items[{{ $index }}][is_active]" value="1"
                                           id="item_active_{{ $index }}" {{ ($item['is_active'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="item_active_{{ $index }}">Đang bật</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-4 removeAdItem">
                                    <i class="fas fa-trash"></i> Xóa item
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted mb-4">Mỗi item gồm tiêu đề, ảnh và link. Khi đọc chapter, hệ thống sẽ random item và lấy đúng số lượng đã cấu hình.</small>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Cập nhật quảng cáo' : 'Lưu quảng cáo' }}
            </button>
            <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var modeSelect = document.getElementById('display_mode');
    var freqSelect = document.getElementById('frequency');
    var afterClickSelect = document.getElementById('after_click');

    function toggleByMode() {
        var mode = modeSelect.value;
        // Hiện phần tử nếu nó có class của mode đang chọn (logic OR — tránh bị lượt sau ghi đè
        // với phần tử mang nhiều class, vd "mode-popup mode-click_anywhere").
        document.querySelectorAll('.mode-banner, .mode-popup, .mode-click_anywhere, .mode-chapter')
            .forEach(function (el) {
                el.style.display = el.classList.contains('mode-' + mode) ? '' : 'none';
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

    ['chapter_start', 'chapter_interval', 'chapter_inline_first_after', 'chapter_inline_every'].forEach(function (name) {
        var field = document.querySelector('[name="' + name + '"]');
        if (field && field.closest('.form-group')) {
            field.closest('.form-group').style.display = 'none';
        }
    });
    var inlineCount = document.querySelector('[name="chapter_inline_count"]');
    if (inlineCount && inlineCount.closest('.form-group')) {
        var label = inlineCount.closest('.form-group').querySelector('label');
        if (label) label.textContent = 'Số item chèn trong 1 trang';
        if (!inlineCount.closest('.form-group').querySelector('.chapter-inline-help')) {
            inlineCount.insertAdjacentHTML('afterend', '<small class="form-text text-muted chapter-inline-help">Áp dụng cho mọi chương. Hệ thống tự chọn vị trí chèn và random item theo số lượng này.</small>');
        }
    }

    // Preview ảnh upload
    if (inlineCount && inlineCount.closest('.mode-chapter')) {
        var chapterConfig = inlineCount.closest('.mode-chapter');
        var chapterTitle = chapterConfig.querySelector('h5');
        if (chapterTitle) chapterTitle.innerHTML = '<i class="fas fa-book-open"></i> Cấu hình khi đọc chapter';
        Array.prototype.forEach.call(chapterConfig.children, function (child) {
            if (child.tagName === 'SMALL') child.style.display = 'none';
        });
    }
    var hideVipLabel = document.querySelector('label[for="hide_for_vip"]');
    if (hideVipLabel) hideVipLabel.textContent = 'Ẩn với user VIP';
    var requireClickLabel = document.querySelector('label[for="require_click"]');
    if (requireClickLabel) requireClickLabel.textContent = 'Phải click mới đọc tiếp';

    var adItems = document.getElementById('adItems');
    var addAdItem = document.getElementById('addAdItem');
    var itemIndex = adItems ? adItems.querySelectorAll('.ad-item').length : 0;

    function itemTemplate(index) {
        return '' +
            '<div class="ad-item border rounded p-3 mb-3" data-index="' + index + '">' +
                '<input type="hidden" name="items[' + index + '][id]" value="">' +
                '<input type="hidden" class="item-delete" name="items[' + index + '][delete]" value="0">' +
                '<div class="row">' +
                    '<div class="form-group col-md-3"><label>Tiêu đề</label><input type="text" name="items[' + index + '][title]" class="form-control" placeholder="Find Your Path"></div>' +
                    '<div class="form-group col-md-3"><label>Link</label><input type="text" name="items[' + index + '][link]" class="form-control" placeholder="https://..."></div>' +
                    '<div class="form-group col-md-6"><label>Ảnh item</label>' + window.buildImageUpload({ name: 'items[' + index + '][image_file]', urlName: 'items[' + index + '][image_url]', removeName: 'items[' + index + '][image_remove]', height: 52, id: 'imgup_aditem_' + index }) + '</div>' +
                '</div>' +
                '<div class="d-flex align-items-center" style="gap:18px">' +
                    '<div class="form-group mb-0" style="width:120px"><label>Thứ tự</label><input type="number" name="items[' + index + '][sort_order]" class="form-control" min="0" value="' + index + '"></div>' +
                    '<div class="form-check mt-4"><input type="hidden" name="items[' + index + '][is_active]" value="0"><input class="form-check-input" type="checkbox" name="items[' + index + '][is_active]" value="1" id="item_active_' + index + '" checked><label class="form-check-label" for="item_active_' + index + '">Đang bật</label></div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger mt-4 removeAdItem"><i class="fas fa-trash"></i> Xóa item</button>' +
                '</div>' +
            '</div>';
    }

    if (addAdItem && adItems) {
        addAdItem.addEventListener('click', function () {
            adItems.insertAdjacentHTML('beforeend', itemTemplate(itemIndex++));
        });
        adItems.addEventListener('click', function (event) {
            var button = event.target.closest('.removeAdItem');
            if (!button) return;
            var row = button.closest('.ad-item');
            var deleteInput = row.querySelector('.item-delete');
            var idInput = row.querySelector('input[name$="[id]"]');
            if (idInput && idInput.value) {
                deleteInput.value = '1';
                row.style.display = 'none';
            } else {
                row.remove();
            }
        });
    }

})();
</script>
@endpush
@endsection
