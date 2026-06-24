{{-- Tên thể loại --}}
<div class="form-group">
    <label for="name">Tên thể loại <span class="text-danger">*</span></label>
    <input type="text" 
           name="name" 
           id="name" 
           class="form-control @error('name') is-invalid @enderror" 
           value="{{ old('name', $genre->name) }}" 
           required>
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

{{-- Slug --}}
<div class="form-group">
    <label for="slug">Slug (URL thân thiện)</label>
    <input type="text" 
           name="slug" 
           id="slug"
           class="form-control @error('slug') is-invalid @enderror"
           value="{{ old('slug', optional($genre->slug)->slug) }}"
           placeholder="tu-dong-tao-theo-ten-the-loai">
    <small class="form-text text-muted">
        URL: <code>/genres/<span id="slug-preview">{{ old('slug', optional($genre->slug)->slug) ?: 'slug-here' }}</span></code>
    </small>
    @error('slug')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

{{-- Ảnh đại diện --}}
<div class="form-group">
    <x-admin.image-upload
        name="cover_image"
        label="Ảnh thể loại"
        :height="120"
        :current="$genre->cover_image ? asset($genre->cover_image) : null"
        accept="image/jpeg,image/png,image/webp"
        hint="Ảnh JPG, PNG hoặc WebP, tối đa 4 MB. Khuyến nghị dùng ảnh ngang." />
    @error('cover_image')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

{{-- Mô tả --}}
<div class="form-group">
    <label for="description">Mô tả ngắn</label>
    <textarea name="description" 
              id="description" 
              class="form-control @error('description') is-invalid @enderror" 
              rows="3">{{ old('description', $genre->description) }}</textarea>
    <small class="form-text text-muted">Mô tả ngắn gọn về thể loại này</small>
    @error('description')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

{{-- Hot: hiện ở dropdown thể loại trên header --}}
<div class="form-group">
    <div class="form-check">
        <input type="hidden" name="is_hot" value="0">
        <input type="checkbox" name="is_hot" value="1" id="is_hot" class="form-check-input"
               {{ old('is_hot', $genre->is_hot ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_hot">
            <strong>Hot</strong> — hiển thị ở menu thể loại (dropdown) trên header
        </label>
    </div>
    <small class="form-text text-muted">Mặc định tắt. Khi có thể loại Hot, dropdown header chỉ hiện các thể loại Hot.</small>
</div>

@push('scripts')
<script>
(function () {
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');
    const preview = document.querySelector('#slug-preview');

    if (!nameInput || !slugInput) return;

    function slugifyText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'd')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .replace(/-{2,}/g, '-');
    }

    let manualSlug = slugInput.value.length > 0
        && slugInput.value !== slugifyText(nameInput.value);

    function updatePreview() {
        if (preview) {
            preview.textContent = slugInput.value || slugifyText(nameInput.value) || 'slug-here';
        }
    }

    nameInput.addEventListener('input', function () {
        if (!manualSlug) {
            slugInput.value = slugifyText(nameInput.value);
        }
        updatePreview();
    });

    slugInput.addEventListener('input', function () {
        manualSlug = slugInput.value.length > 0;
        slugInput.value = slugifyText(slugInput.value);
        updatePreview();
    });

    if (!slugInput.value && nameInput.value) {
        slugInput.value = slugifyText(nameInput.value);
    }
    updatePreview();

})();
</script>
@endpush
