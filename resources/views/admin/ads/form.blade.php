@extends('layout.admin')
@section('template_title', $ad->exists ? 'Chỉnh sửa: ' . $ad->name : 'Thêm quảng cáo mới')

@push('styles')
<style>
/* ===== TYPE PICKER ===== */
.ad-type-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:24px }
.ad-type-card  { cursor:pointer; border:2px solid #dee2e6; border-radius:10px; padding:16px 12px;
                 text-align:center; transition:all .15s; background:#fff; position:relative }
.ad-type-card:hover { border-color:#adb5bd; background:#f8f9fa }
.ad-type-card.active { border-color:#007bff; background:#e8f0fe }
.ad-type-card .ad-type-icon { font-size:28px; margin-bottom:8px; display:block }
.ad-type-card .ad-type-name { font-weight:600; font-size:13px }
.ad-type-card .ad-type-hint { font-size:11px; color:#888; margin-top:4px; line-height:1.4 }
.ad-type-card input[type=radio] { position:absolute; opacity:0; width:0; height:0 }
.ad-type-card .check-mark { position:absolute; top:8px; right:8px; width:18px; height:18px;
    border-radius:50%; border:2px solid #dee2e6; background:#fff; display:flex; align-items:center; justify-content:center }
.ad-type-card.active .check-mark { background:#007bff; border-color:#007bff; color:#fff }
.ad-type-card.active .check-mark::after { content:'✓'; font-size:11px; font-weight:700 }

/* ===== SECTION PANELS ===== */
.ad-panel { display:none }
.ad-panel.active { display:block }

/* ===== ITEMS LIST ===== */
.ad-item-row { background:#f8f9fa; border-radius:8px; padding:14px; margin-bottom:10px; border:1px solid #e9ecef }
.ad-item-row .drag-handle { cursor:grab; color:#aaa; margin-right:8px }

@media(max-width:768px) {
    .ad-type-cards { grid-template-columns:repeat(2,1fr) }
}
</style>
@endpush

@section('content')
@php
    $isEdit      = $ad->exists;
    $mode        = old('display_mode', $ad->display_mode ?? 'banner');
    $selPages    = old('pages', $ad->pages ?? ['all']);
    $itemRows    = old('items');
    if ($itemRows === null) {
        $itemRows = $ad->items->map(fn($i) => [
            'id'        => $i->id,
            'title'     => $i->title,
            'image_url' => $i->image_url,
            'script_code' => $i->script_code,
            'link'      => $i->link,
            'sort_order'=> $i->sort_order,
            'is_active' => $i->is_active,
            'image'     => $i->image,
        ])->values()->all();
    }
    if (empty($itemRows)) {
        $itemRows = [['id'=>null,'title'=>'','image_url'=>'','script_code'=>'','link'=>'','sort_order'=>0,'is_active'=>true,'image'=>null]];
    }

    $typeInfo = [
        'banner'         => ['icon'=>'📌', 'name'=>'Banner cố định',    'hint'=>'Hiện ảnh cố định ở vị trí chỉ định (đầu trang, sidebar…)'],
        'click_anywhere' => ['icon'=>'👆', 'name'=>'Click bất kỳ đâu', 'hint'=>'Mở link khi user click bất kỳ vị trí nào trên trang'],
        'popup'          => ['icon'=>'🖼️', 'name'=>'Popup / Overlay',  'hint'=>'Hiện ảnh che màn hình, có đếm ngược đóng'],
        'chapter'        => ['icon'=>'📖', 'name'=>'Trong chapter',     'hint'=>'Chèn ảnh quảng cáo xen kẽ nội dung khi đọc truyện'],
    ];
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.ads.update', $ad) : route('admin.ads.store') }}" enctype="multipart/form-data" id="adForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Có lỗi:</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    {{-- ===== 1. CHỌN LOẠI ===== --}}
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-th-large mr-1"></i> Loại quảng cáo</h3></div>
        <div class="card-body pb-1">
            <div class="ad-type-cards">
                @foreach($typeInfo as $val => $info)
                <label class="ad-type-card {{ $mode === $val ? 'active' : '' }}" data-mode="{{ $val }}">
                    <input type="radio" name="display_mode" value="{{ $val }}" {{ $mode === $val ? 'checked' : '' }}>
                    <div class="check-mark"></div>
                    <span class="ad-type-icon">{{ $info['icon'] }}</span>
                    <div class="ad-type-name">{{ $info['name'] }}</div>
                    <div class="ad-type-hint">{{ $info['hint'] }}</div>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== 2. THÔNG TIN CHUNG ===== --}}
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-info-circle mr-1"></i> Thông tin chung</h3></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Tên quảng cáo <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $ad->name) }}" required placeholder="VD: Banner top trang chủ">
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Ưu tiên <small class="text-muted">(số nhỏ = trước)</small></label>
                        <input type="number" name="priority" class="form-control" min="0"
                               value="{{ old('priority', $ad->priority ?? 0) }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group w-100">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', $ad->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold" for="is_active">Đang bật</label>
                        </div>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" name="hide_for_vip" id="hide_for_vip"
                                   value="1" {{ old('hide_for_vip', $ad->hide_for_vip ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="hide_for_vip">Ẩn với VIP</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trang chạy --}}
            <div class="form-group mb-2">
                <label>Chèn ở các trang</label>
                <div class="d-flex flex-wrap" style="gap:14px">
                    @foreach(\App\Models\Ad::PAGES as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pages[]" value="{{ $val }}"
                               id="page_{{ $val }}" {{ in_array($val, $selPages) ? 'checked' : '' }}>
                        <label class="form-check-label" for="page_{{ $val }}">{{ __('messages.ads.pages.'.$val) }}</label>
                    </div>
                    @endforeach
                </div>
                <small class="text-muted">Chọn "Toàn site" để hiện ở mọi nơi</small>
            </div>

            {{-- Lịch hẹn --}}
            <div class="row mt-2">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Bắt đầu</label>
                        <input type="datetime-local" name="start_at" class="form-control"
                               value="{{ old('start_at', $ad->start_at ? $ad->start_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Kết thúc</label>
                        <input type="datetime-local" name="end_at" class="form-control"
                               value="{{ old('end_at', $ad->end_at ? $ad->end_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <small class="text-muted pb-1">Để trống = không giới hạn thời gian</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PANEL: BANNER ===== --}}
    <div class="ad-panel {{ $mode === 'banner' ? 'active' : '' }}" id="panel-banner">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">📌 Cấu hình Banner cố định</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <x-admin.image-upload name="image_file" label="Ảnh banner" :height="100"
                                :current="$ad->image ?? null"
                                urlName="image_url" :urlValue="old('image_url', $ad->image_url)"
                                hint="Upload hoặc dán URL ảnh ngoài (URL được ưu tiên)" />
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Script quang cao</label>
                            <textarea name="script_code" class="form-control" rows="5"
                                      placeholder="<script>...</script> hoac iframe/html tu network quang cao">{{ old('script_code', $ad->script_code) }}</textarea>
                            <small class="text-muted">Neu nhap script, frontend se render script thay vi anh banner.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Link đích khi click</label>
                            <input type="text" name="link" class="form-control" placeholder="https://..."
                                   value="{{ old('link', $ad->link) }}">
                        </div>
                        <div class="form-group">
                            <label>Vị trí hiển thị <span class="text-danger">*</span></label>
                            <select name="placement" class="form-control">
                                @foreach(\App\Models\Ad::PLACEMENTS as $val => $label)
                                <option value="{{ $val }}" {{ old('placement', $ad->placement ?? 'top') === $val ? 'selected' : '' }}>
                                    {{ __('messages.ads.placements.'.$val) }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Slot cố định trên layout trang</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PANEL: CLICK ANYWHERE ===== --}}
    <div class="ad-panel {{ $mode === 'click_anywhere' ? 'active' : '' }}" id="panel-click_anywhere">
        <div class="card mb-3">
            <div class="card-header bg-warning">
                <h3 class="card-title mb-0">👆 Cấu hình Click bất kỳ đâu</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle"></i>
                    Khi user click bất kỳ đâu trên trang, hệ thống sẽ mở link đích trong tab mới. Navigation của user vẫn diễn ra bình thường.
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Link đích</label>
                            <input type="text" name="link" class="form-control" placeholder="https://..."
                                   value="{{ old('link', $ad->link) }}">
                            <small class="text-muted">Tab mới sẽ mở link này khi user click</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Script quang cao</label>
                            <textarea name="script_code" class="form-control" rows="5"
                                      placeholder="<script>...</script> hoac iframe/html tu network quang cao">{{ old('script_code', $ad->script_code) }}</textarea>
                            <small class="text-muted">Co the dung script thay cho link dich.</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tần suất hiển thị</label>
                            <select name="frequency" id="freq_click" class="form-control">
                                @foreach(\App\Models\Ad::FREQUENCIES as $val => $label)
                                <option value="{{ $val }}" {{ old('frequency', $ad->frequency ?? 'once_session') === $val ? 'selected' : '' }}>
                                    {{ __('messages.ads.frequencies.'.$val) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 freq-n-click">
                        <div class="form-group">
                            <label>Mỗi N lượt xem trang</label>
                            <input type="number" name="frequency_value" class="form-control" min="1"
                                   value="{{ old('frequency_value', $ad->frequency_value ?? 3) }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Sau khi đã click</label>
                            <select name="after_click" id="after_click_click" class="form-control">
                                @foreach(\App\Models\Ad::AFTER_CLICKS as $val => $label)
                                <option value="{{ $val }}" {{ old('after_click', $ad->after_click ?? 'stop_session') === $val ? 'selected' : '' }}>
                                    {{ __('messages.ads.after_clicks.'.$val) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 cooldown-click">
                        <div class="form-group">
                            <label>Chờ lại (giây)</label>
                            <input type="number" name="cooldown_seconds" class="form-control" min="0"
                                   value="{{ old('cooldown_seconds', $ad->cooldown_seconds ?? 300) }}"
                                   placeholder="VD: 300 = 5 phút">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PANEL: POPUP ===== --}}
    <div class="ad-panel {{ $mode === 'popup' ? 'active' : '' }}" id="panel-popup">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h3 class="card-title mb-0">🖼️ Cấu hình Popup / Overlay</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <x-admin.image-upload name="image_file" label="Ảnh popup" :height="120"
                                :current="$ad->image ?? null"
                                urlName="image_url" :urlValue="old('image_url', $ad->image_url)"
                                hint="Ảnh sẽ hiện giữa màn hình, nền mờ phía sau" />
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label>Script quang cao</label>
                            <textarea name="script_code" class="form-control" rows="5"
                                      placeholder="<script>...</script> hoac iframe/html tu network quang cao">{{ old('script_code', $ad->script_code) }}</textarea>
                            <small class="text-muted">Neu nhap script, popup se render script thay vi anh.</small>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Link đích khi click ảnh</label>
                            <input type="text" name="link" class="form-control" placeholder="https://..."
                                   value="{{ old('link', $ad->link) }}">
                        </div>
                        <div class="form-group">
                            <label>Đếm ngược đóng (giây)</label>
                            <input type="number" name="delay_seconds" class="form-control" min="0"
                                   value="{{ old('delay_seconds', $ad->delay_seconds ?? 5) }}"
                                   placeholder="0 = đóng ngay không cần chờ">
                            <small class="text-muted">User phải chờ hết thời gian mới có nút đóng</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tần suất hiển thị</label>
                            <select name="frequency" id="freq_popup" class="form-control">
                                @foreach(\App\Models\Ad::FREQUENCIES as $val => $label)
                                <option value="{{ $val }}" {{ old('frequency', $ad->frequency ?? 'once_session') === $val ? 'selected' : '' }}>
                                    {{ __('messages.ads.frequencies.'.$val) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 freq-n-popup">
                        <div class="form-group">
                            <label>Mỗi N lượt xem</label>
                            <input type="number" name="frequency_value" class="form-control" min="1"
                                   value="{{ old('frequency_value', $ad->frequency_value ?? 1) }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Sau khi đã click</label>
                            <select name="after_click" id="after_click_popup" class="form-control">
                                @foreach(\App\Models\Ad::AFTER_CLICKS as $val => $label)
                                <option value="{{ $val }}" {{ old('after_click', $ad->after_click ?? 'none') === $val ? 'selected' : '' }}>
                                    {{ __('messages.ads.after_clicks.'.$val) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 cooldown-popup">
                        <div class="form-group">
                            <label>Chờ lại (giây)</label>
                            <input type="number" name="cooldown_seconds" class="form-control" min="0"
                                   value="{{ old('cooldown_seconds', $ad->cooldown_seconds ?? 0) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PANEL: CHAPTER ===== --}}
    <div class="ad-panel {{ $mode === 'chapter' ? 'active' : '' }}" id="panel-chapter">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">📖 Cấu hình quảng cáo trong Chapter</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label>Số ảnh chèn mỗi trang <span class="text-danger">*</span></label>
                            <input type="number" name="chapter_inline_count" class="form-control" min="1" max="20"
                                   value="{{ old('chapter_inline_count', $ad->chapter_inline_count ?? 1) }}">
                            <small class="text-muted">Hệ thống random từ pool items bên dưới</small>
                        </div>
                    </div>
                    <div class="col-md-5 d-flex align-items-end" style="gap:24px">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="require_click" id="require_click"
                                   value="1" {{ old('require_click', $ad->require_click ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="require_click">
                                <strong>Phải click quảng cáo mới đọc tiếp</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label>Script quang cao chung</label>
                            <textarea name="script_code" class="form-control" rows="4"
                                      placeholder="<script>...</script> hoac iframe/html tu network quang cao">{{ old('script_code', $ad->script_code) }}</textarea>
                            <small class="text-muted">Dung khi khong tao item rieng ben duoi.</small>
                        </div>
                    </div>
                </div>

                <hr>
                {{-- Items pool --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Pool ảnh quảng cáo</h5>
                        <small class="text-muted">Hệ thống sẽ random trong pool này khi chèn vào chapter</small>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addAdItem">
                        <i class="fas fa-plus"></i> Thêm ảnh
                    </button>
                </div>
                <div id="adItems">
                    @foreach($itemRows as $index => $item)
                    <div class="ad-item-row d-flex align-items-center" data-index="{{ $index }}" style="gap:12px">
                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                        <input type="hidden" class="item-delete" name="items[{{ $index }}][delete]" value="0">
                        <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                        <div style="flex:0 0 160px">
                            <x-admin.image-upload name="items[{{ $index }}][image_file]"
                                urlName="items[{{ $index }}][image_url]"
                                removeName="items[{{ $index }}][image_remove]"
                                :current="$item['image'] ?? null" :height="60"
                                :urlValue="$item['image_url'] ?? ''" />
                        </div>
                        <div style="flex:1">
                            <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm mb-1"
                                   placeholder="Tiêu đề (tuỳ chọn)" value="{{ $item['title'] ?? '' }}">
                            <input type="text" name="items[{{ $index }}][link]" class="form-control form-control-sm"
                                   placeholder="Link đích https://..." value="{{ $item['link'] ?? '' }}">
                            <textarea name="items[{{ $index }}][script_code]" class="form-control form-control-sm mt-1" rows="3"
                                      placeholder="Script/iframe/html quang cao (tuy chon)">{{ $item['script_code'] ?? '' }}</textarea>
                        </div>
                        <div style="flex:0 0 80px">
                            <label class="small mb-1">Thứ tự</label>
                            <input type="number" name="items[{{ $index }}][sort_order]" class="form-control form-control-sm"
                                   min="0" value="{{ $item['sort_order'] ?? $index }}">
                        </div>
                        <div>
                            <div class="form-check mb-1">
                                <input type="hidden" name="items[{{ $index }}][is_active]" value="0">
                                <input class="form-check-input" type="checkbox" name="items[{{ $index }}][is_active]"
                                       value="1" id="ia_{{ $index }}" {{ ($item['is_active'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="ia_{{ $index }}">Bật</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger removeAdItem">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="card">
        <div class="card-body py-3 d-flex" style="gap:10px">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Cập nhật' : 'Lưu quảng cáo' }}
            </button>
            <a href="{{ route('admin.ads.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

</form>
@endsection

@push('scripts')
<script>
(function () {
    // ===== TYPE PICKER =====
    var cards = document.querySelectorAll('.ad-type-card');
    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            var mode = card.dataset.mode;
            card.querySelector('input[type=radio]').checked = true;
            cards.forEach(function (c) { c.classList.remove('active') });
            card.classList.add('active');
            document.querySelectorAll('.ad-panel').forEach(function (p) { p.classList.remove('active') });
            var panel = document.getElementById('panel-' + mode);
            if (panel) panel.classList.add('active');
        });
    });

    // ===== FREQ / COOLDOWN toggles for click_anywhere =====
    function bindFreqToggle(selectId, nClass, cooldownClass, afterClickId) {
        var sel = document.getElementById(selectId);
        var ac  = document.getElementById(afterClickId);
        if (!sel) return;
        function doFreq() {
            document.querySelectorAll('.' + nClass).forEach(function (el) {
                el.style.display = sel.value === 'every_n_views' ? '' : 'none';
            });
        }
        function doCooldown() {
            if (!ac) return;
            document.querySelectorAll('.' + cooldownClass).forEach(function (el) {
                el.style.display = ac.value === 'cooldown' ? '' : 'none';
            });
        }
        sel.addEventListener('change', doFreq);
        if (ac) ac.addEventListener('change', doCooldown);
        doFreq(); doCooldown();
    }
    bindFreqToggle('freq_click',  'freq-n-click',  'cooldown-click',  'after_click_click');
    bindFreqToggle('freq_popup',  'freq-n-popup',  'cooldown-popup',  'after_click_popup');

    // ===== CHAPTER ITEMS =====
    var adItems  = document.getElementById('adItems');
    var addBtn   = document.getElementById('addAdItem');
    var itemIdx  = adItems ? adItems.querySelectorAll('.ad-item-row').length : 0;

    function buildItemRow(idx) {
        return '<div class="ad-item-row d-flex align-items-center" data-index="' + idx + '" style="gap:12px">' +
            '<input type="hidden" name="items[' + idx + '][id]" value="">' +
            '<input type="hidden" class="item-delete" name="items[' + idx + '][delete]" value="0">' +
            '<span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
            '<div style="flex:0 0 160px">' +
                window.buildImageUpload({ name:'items['+idx+'][image_file]', urlName:'items['+idx+'][image_url]', removeName:'items['+idx+'][image_remove]', height:60, id:'imgup_item_'+idx }) +
            '</div>' +
            '<div style="flex:1">' +
                '<input type="text" name="items['+idx+'][title]" class="form-control form-control-sm mb-1" placeholder="Tiêu đề (tuỳ chọn)">' +
                '<input type="text" name="items['+idx+'][link]" class="form-control form-control-sm" placeholder="Link đích https://...">' +
                '<textarea name="items['+idx+'][script_code]" class="form-control form-control-sm mt-1" rows="3" placeholder="Script/iframe/html quang cao (tuy chon)"></textarea>' +
            '</div>' +
            '<div style="flex:0 0 80px"><label class="small mb-1">Thứ tự</label>' +
                '<input type="number" name="items['+idx+'][sort_order]" class="form-control form-control-sm" min="0" value="'+idx+'">' +
            '</div>' +
            '<div>' +
                '<div class="form-check mb-1"><input type="hidden" name="items['+idx+'][is_active]" value="0">' +
                '<input class="form-check-input" type="checkbox" name="items['+idx+'][is_active]" value="1" id="ia_'+idx+'" checked>' +
                '<label class="form-check-label small" for="ia_'+idx+'">Bật</label></div>' +
                '<button type="button" class="btn btn-sm btn-outline-danger removeAdItem"><i class="fas fa-trash"></i></button>' +
            '</div></div>';
    }

    if (addBtn && adItems) {
        addBtn.addEventListener('click', function () {
            adItems.insertAdjacentHTML('beforeend', buildItemRow(itemIdx++));
        });
        adItems.addEventListener('click', function (e) {
            var btn = e.target.closest('.removeAdItem');
            if (!btn) return;
            var row = btn.closest('.ad-item-row');
            var del = row.querySelector('.item-delete');
            var id  = row.querySelector('input[name$="[id]"]');
            if (id && id.value) { del.value = '1'; row.style.display = 'none'; }
            else row.remove();
        });
    }

    // Fields in hidden panels share names like link/script_code, so disable them before submit.
    var form = document.getElementById('adForm');
    if (form) {
        form.addEventListener('submit', function () {
            document.querySelectorAll('.ad-panel:not(.active) input, .ad-panel:not(.active) select, .ad-panel:not(.active) textarea').forEach(function (el) {
                el.disabled = true;
            });
        });
    }
})();
</script>
@endpush
