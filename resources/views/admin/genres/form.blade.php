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

@push('scripts')
<script>
(function () {
    const nameInput = document.querySelector('#name');
    const slugInput = document.querySelector('#slug');
    const preview = document.querySelector('#slug-preview');

    if (!nameInput || !slugInput) return;

    let manualSlug = slugInput.value.length > 0;

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
