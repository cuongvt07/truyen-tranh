@extends('layout.admin')

@section('template_title', $mode === 'create' ? 'Thêm mới Nhân vật' : 'Chỉnh sửa Nhân vật: ' . $item->name)

@section('content')
@php 
    $action = $mode === 'create' ? route('admin.characters.store') : route('admin.characters.update', $item->id); 
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $mode === 'create' ? 'Thêm mới Nhân vật' : 'Chỉnh sửa Nhân vật' }}</h3>
            </div>
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @if($mode === 'edit') @method('PATCH') @endif
                
                <div class="card-body">
                    {{-- Tên nhân vật --}}
                    <div class="form-group">
                        <label>Tên nhân vật <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $item->name) }}" 
                               required 
                               autofocus
                               placeholder="Nhập tên nhân vật">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Loại nhân vật --}}
                    <div class="form-group">
                        <label>Loại nhân vật</label>
                        <select name="type" class="form-control @error('type') is-invalid @enderror">
                            <option value="0" {{ old('type', $item->type) == 0 ? 'selected' : '' }}>Nhân vật chính</option>
                            <option value="1" {{ old('type', $item->type) == 1 ? 'selected' : '' }}>Nhân vật phụ</option>
                            <option value="2" {{ old('type', $item->type) == 2 ? 'selected' : '' }}>Khác</option>
                        </select>
                        @error('type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Mô tả --}}
                    <div class="form-group">
                        <label>Mô tả / Giới thiệu</label>
                        <textarea name="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="6"
                                  placeholder="Giới thiệu ngắn gọn về nhân vật này...">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Thông tin về nhân vật, vai trò, đặc điểm...</small>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ $mode === 'create' ? 'Lưu nhân vật' : 'Cập nhật' }}
                    </button>
                    <a href="{{ route('admin.characters.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar for image --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ảnh đại diện</h3>
            </div>
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                @if($mode === 'edit') @method('PATCH') @endif
                
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img id="cover-preview" 
                             src="{{ $item->photo ?: asset('static/account/images/no-ava.jpg') }}" 
                             alt="Preview"
                             style="width:150px;height:150px;object-fit:cover;border-radius:50%;box-shadow:0 4px 12px rgba(0,0,0,.2);border:3px solid #fff">
                    </div>
                    
                    <div class="form-group">
                        <label class="btn btn-sm btn-outline-primary btn-block" for="cover-input">
                            <i class="fas fa-upload"></i> Chọn ảnh
                        </label>
                        <input type="file" 
                               name="photo" 
                               id="cover-input" 
                               class="d-none @error('photo') is-invalid @enderror"
                               accept="image/*">
                        @error('photo')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-0">
                        <input type="text" 
                               name="photo_url" 
                               class="form-control form-control-sm @error('photo_url') is-invalid @enderror" 
                               placeholder="Hoặc nhập URL ảnh..."
                               value="{{ old('photo_url') }}">
                        @error('photo_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Link ảnh từ nguồn khác</small>
                    </div>
                </div>
            </form>
        </div>

        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Thông tin</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @if($item->exists)
                        <li class="mb-2">
                            <strong>ID:</strong> <span class="text-muted">{{ $item->id }}</span>
                        </li>
                        <li class="mb-2">
                            <strong>Số truyện:</strong> 
                            <span class="badge badge-info">{{ $item->articles()->count() }}</span>
                        </li>
                        <li class="mb-2">
                            <strong>Tạo lúc:</strong><br>
                            <small class="text-muted">{{ $item->created_at?->format('d/m/Y H:i') }}</small>
                        </li>
                    @else
                        <li class="text-muted">
                            <small>Thông tin sẽ hiển thị sau khi lưu</small>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('cover-input')?.addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('cover-preview').src = ev.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
@endpush
@endsection
