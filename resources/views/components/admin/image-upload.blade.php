@props([
    'name',                 // tên input file (vd 'logo_file', 'photo', 'image_file')
    'label' => '',          // nhãn
    'current' => null,      // URL ảnh hiện tại để preview (đã resolve sẵn, vd asset('storage/...'))
    'urlName' => null,      // tên ô URL ảnh ngoài (null = không có)
    'urlValue' => '',       // giá trị URL hiện tại
    'removeName' => null,   // tên hidden cờ xoá (mặc định = {name}_remove)
    'accept' => 'image/*',  // kiểu file
    'hint' => null,         // dòng gợi ý
    'height' => 120,        // chiều cao preview (px)
])
@php
    $uid = 'imgup_' . preg_replace('/[^a-z0-9]+/i', '_', $name) . '_' . substr(md5($name . $label), 0, 5);
    $removeField = $removeName ?? ($name . '_remove');
    $hasCurrent = !empty($current);
@endphp
<div class="image-upload" data-image-upload>
    @if($label !== '')<label class="d-block font-weight-bold">{{ $label }}</label>@endif

    <div class="image-upload__preview mb-2 {{ $hasCurrent ? '' : 'd-none' }}">
        <img src="{{ $current ?? '' }}" alt=""
             style="height:{{ $height }}px;max-width:100%;border:1px solid #ddd;border-radius:6px;object-fit:cover;background:#f5f5f5">
    </div>

    {{-- cờ xoá: 1 = yêu cầu xoá ảnh hiện có --}}
    <input type="hidden" name="{{ $removeField }}" value="0" data-image-remove>

    <div class="d-flex flex-wrap align-items-center" style="gap:8px">
        <label class="btn btn-sm btn-outline-primary mb-0" for="{{ $uid }}">
            <i class="fas fa-upload"></i> Chọn ảnh
        </label>
        <button type="button" class="btn btn-sm btn-outline-danger mb-0 {{ $hasCurrent ? '' : 'd-none' }}" data-image-clear>
            <i class="fas fa-times"></i> Xoá ảnh
        </button>
        <span class="text-muted small" data-image-filename></span>
    </div>
    <input type="file" name="{{ $name }}" id="{{ $uid }}" class="d-none" accept="{{ $accept }}" data-image-input>

    @if($urlName)
        <input type="text" name="{{ $urlName }}" class="form-control form-control-sm mt-2"
               value="{{ $urlValue }}" placeholder="Hoặc dán URL ảnh ngoài...">
    @endif
    @if($hint)<small class="form-text text-muted">{{ $hint }}</small>@endif
</div>

@once
<script>
(function () {
    // Builder để các form có dòng thêm động (ads items, affiliate) tạo widget giống hệt.
    // Markup khớp với component → handler delegated bên dưới tự xử lý preview/xoá.
    window.buildImageUpload = function (opts) {
        opts = opts || {};
        var fileName = opts.name || '';
        var urlName = opts.urlName || '';
        var removeName = opts.removeName || (fileName + '_remove');
        var id = opts.id || ('imgup_' + Math.random().toString(36).slice(2));
        var accept = opts.accept || 'image/*';
        var h = opts.height || 90;
        var label = opts.label ? '<label class="d-block font-weight-bold">' + opts.label + '</label>' : '';
        var urlHtml = urlName
            ? '<input type="text" name="' + urlName + '" class="form-control form-control-sm mt-2" placeholder="Hoặc dán URL ảnh ngoài...">'
            : '';
        return '<div class="image-upload" data-image-upload>' + label
            + '<div class="image-upload__preview mb-2 d-none"><img src="" alt="" style="height:' + h + 'px;max-width:100%;border:1px solid #ddd;border-radius:6px;object-fit:cover;background:#f5f5f5"></div>'
            + '<input type="hidden" name="' + removeName + '" value="0" data-image-remove>'
            + '<div class="d-flex flex-wrap align-items-center" style="gap:8px">'
            +   '<label class="btn btn-sm btn-outline-primary mb-0" for="' + id + '"><i class="fas fa-upload"></i> Chọn ảnh</label>'
            +   '<button type="button" class="btn btn-sm btn-outline-danger mb-0 d-none" data-image-clear><i class="fas fa-times"></i> Xoá ảnh</button>'
            +   '<span class="text-muted small" data-image-filename></span>'
            + '</div>'
            + '<input type="file" name="' + fileName + '" id="' + id + '" class="d-none" accept="' + accept + '" data-image-input>'
            + urlHtml + '</div>';
    };
</script>
<script>
(function () {
    // Preview nhanh bằng object URL (không upload, không đọc base64 → nhẹ)
    document.addEventListener('change', function (e) {
        var input = e.target.closest('[data-image-upload] [data-image-input]');
        if (!input) return;
        var wrap = input.closest('[data-image-upload]');
        var file = input.files && input.files[0];
        if (!file) return;
        var prev = wrap.querySelector('.image-upload__preview');
        var img = prev ? prev.querySelector('img') : null;
        var clearBtn = wrap.querySelector('[data-image-clear]');
        var flag = wrap.querySelector('[data-image-remove]');
        var nameEl = wrap.querySelector('[data-image-filename]');
        if (img) {
            if (img.dataset.objurl) URL.revokeObjectURL(img.dataset.objurl);
            var u = URL.createObjectURL(file);
            img.src = u; img.dataset.objurl = u;
        }
        if (prev) prev.classList.remove('d-none');
        if (clearBtn) clearBtn.classList.remove('d-none');
        if (flag) flag.value = '0';
        if (nameEl) nameEl.textContent = file.name;
    });

    // Xoá ảnh: reset input + ẩn preview + bật cờ xoá (báo backend xoá ảnh cũ)
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-image-upload] [data-image-clear]');
        if (!btn) return;
        var wrap = btn.closest('[data-image-upload]');
        var input = wrap.querySelector('[data-image-input]');
        var prev = wrap.querySelector('.image-upload__preview');
        var img = prev ? prev.querySelector('img') : null;
        var flag = wrap.querySelector('[data-image-remove]');
        var nameEl = wrap.querySelector('[data-image-filename]');
        var urlField = wrap.querySelector('input[type="text"]');
        if (input) input.value = '';
        if (urlField) urlField.value = ''; // xoá luôn URL ngoài (field lưu dạng URL)
        if (img && img.dataset.objurl) { URL.revokeObjectURL(img.dataset.objurl); img.dataset.objurl = ''; }
        if (img) img.src = '';
        if (prev) prev.classList.add('d-none');
        btn.classList.add('d-none');
        if (flag) flag.value = '1';
        if (nameEl) nameEl.textContent = '';
    });
})();
</script>
@endonce
