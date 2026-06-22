@extends('layout.admin')

@section('template_title', $mode === 'create' ? 'Thêm mới Nhân vật' : 'Chỉnh sửa Nhân vật: ' . $item->name)

@section('content')
@php 
    $action = $mode === 'create' ? route('admin.characters.store') : route('admin.characters.update', $item->id); 
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
@csrf
@if($mode === 'edit') @method('PATCH') @endif
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $mode === 'create' ? 'Thêm mới Nhân vật' : 'Chỉnh sửa Nhân vật' }}</h3>
            </div>
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

                    {{-- Truyện nhân vật xuất hiện (gán nhiều truyện) --}}
                    <div class="form-group">
                        <label for="articles">Xuất hiện trong truyện</label>
                        <select name="articles[]" id="articles" class="form-control character-article-select @error('articles') is-invalid @enderror" multiple>
                            @foreach($articleOptions as $option)
                                <option value="{{ $option->id }}" {{ in_array($option->id, old('articles', $selectedArticleIds)) ? 'selected' : '' }}>
                                    {{ $option->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('articles.*')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        <small class="form-text text-muted">Chọn các truyện mà nhân vật này xuất hiện. Có thể gõ để tìm.</small>
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
        </div>
    </div>

    {{-- Sidebar for image --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ảnh đại diện</h3>
            </div>
                <div class="card-body text-center">
                    <x-admin.image-upload name="photo" :height="150"
                        :current="$item->photo ?: null"
                        urlName="photo_url" :urlValue="old('photo_url')"
                        hint="Link ảnh từ nguồn khác" />
                </div>
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
</form>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush

@section('ArticleScripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            $('.character-article-select').select2({
                theme: 'bootstrap4',
                placeholder: 'Chọn truyện...',
                width: '100%',
                allowClear: true,
            });
        });
    </script>
@endsection
