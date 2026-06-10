{{-- Tên tác giả --}}
<div class="form-group">
    <label for="name">Tên tác giả <span class="text-danger">*</span></label>
    <input type="text" 
           name="name" 
           id="name" 
           class="form-control @error('name') is-invalid @enderror" 
           value="{{ old('name', $author->name) }}" 
           required
           placeholder="Nhập tên tác giả">
    @error('name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
    <small class="form-text text-muted">Tên đầy đủ của tác giả (VD: Nguyễn Nhật Ánh, Kim Dung...)</small>
</div>

{{-- Mô tả --}}
<div class="form-group">
    <label for="description">Mô tả / Tiểu sử ngắn</label>
    <textarea name="description" 
              id="description" 
              class="form-control @error('description') is-invalid @enderror" 
              rows="4"
              placeholder="Giới thiệu ngắn gọn về tác giả (tuỳ chọn)">{{ old('description', $author->description) }}</textarea>
    @error('description')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
    <small class="form-text text-muted">Thông tin về tác giả sẽ hiển thị trên trang công khai</small>
</div>
