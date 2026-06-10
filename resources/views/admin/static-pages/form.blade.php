@extends('layout.admin')

@section('template_title', $page->exists ? 'Chỉnh sửa Trang: ' . $page->title_en : 'Thêm mới Trang tĩnh')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $page->exists ? 'Chỉnh sửa Trang' : 'Thêm mới Trang' }}</h3>
    </div>
    <form method="POST" action="{{ $page->exists ? route('admin.static-pages.update', $page) : route('admin.static-pages.store') }}">
        @csrf
        @if($page->exists) @method('PUT') @endif

        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Basic Info --}}
            <div class="row">
                <div class="form-group col-md-3">
                    <label>Loại trang <span class="text-danger">*</span></label>
                    <select name="page_type" class="form-control @error('page_type') is-invalid @enderror" required>
                        @foreach(\App\Models\StaticPage::TYPES as $value => $label)
                            <option value="{{ $value }}" {{ old('page_type', $page->page_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('page_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-3">
                    <label>Trang cha</label>
                    <select name="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                        <option value="">— Root (Không có cha) —</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ (string) old('parent_id', $page->parent_id) === (string) $parent->id ? 'selected' : '' }}>
                                [{{ $parent->page_type }}] {{ $parent->title_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-4">
                    <label>Slug (URL)</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                           value="{{ old('slug', $page->slug) }}" 
                           placeholder="vd: rules, dmca, terms">
                    @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="form-text text-muted">Slug đang dùng: forum, faq, rules, dmca, terms, feedback, pricing</small>
                </div>
                <div class="form-group col-md-2">
                    <label>Thứ tự sắp xếp</label>
                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                           min="0" value="{{ old('sort_order', $page->sort_order ?? 0) }}">
                    @error('sort_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Section Labels --}}
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Section Label (EN)</label>
                    <input type="text" name="section_label_en" class="form-control @error('section_label_en') is-invalid @enderror" 
                           value="{{ old('section_label_en', $page->section_label_en ?? '') }}"
                           placeholder="VD: DEVELOPERS' INFORMATION">
                    @error('section_label_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="form-text text-muted">Nhãn phân nhóm (dùng cho Forum/FAQ Index)</small>
                </div>
                <div class="form-group col-md-6">
                    <label>Section Label (VI)</label>
                    <input type="text" name="section_label_vi" class="form-control @error('section_label_vi') is-invalid @enderror" 
                           value="{{ old('section_label_vi', $page->section_label_vi ?? '') }}"
                           placeholder="VD: THÔNG TIN PHÁT TRIỂN">
                    @error('section_label_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Titles --}}
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Tiêu đề (EN) <span class="text-danger">*</span></label>
                    <input type="text" name="title_en" class="form-control @error('title_en') is-invalid @enderror" 
                           required value="{{ old('title_en', $page->title_en) }}">
                    @error('title_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Tiêu đề (VI)</label>
                    <input type="text" name="title_vi" class="form-control @error('title_vi') is-invalid @enderror" 
                           value="{{ old('title_vi', $page->title_vi) }}">
                    @error('title_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Excerpts --}}
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Mô tả ngắn (EN)</label>
                    <textarea name="excerpt_en" class="form-control @error('excerpt_en') is-invalid @enderror" 
                              rows="3">{{ old('excerpt_en', $page->excerpt_en) }}</textarea>
                    @error('excerpt_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Mô tả ngắn (VI)</label>
                    <textarea name="excerpt_vi" class="form-control @error('excerpt_vi') is-invalid @enderror" 
                              rows="3">{{ old('excerpt_vi', $page->excerpt_vi) }}</textarea>
                    @error('excerpt_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Content --}}
            <div class="row">
                <div class="form-group col-md-6">
                    <label>Nội dung HTML (EN)</label>
                    <textarea name="content_en" class="form-control js-editor @error('content_en') is-invalid @enderror" 
                              rows="16">{{ old('content_en', $page->content_en) }}</textarea>
                    @error('content_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Nội dung HTML (VI)</label>
                    <textarea name="content_vi" class="form-control js-editor @error('content_vi') is-invalid @enderror" 
                              rows="16">{{ old('content_vi', $page->content_vi) }}</textarea>
                    @error('content_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Status & Options --}}
            <div class="row">
                @if($page->exists && isset($page->status))
                <div class="form-group col-md-3">
                    <label>Trạng thái duyệt</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                        @foreach(\App\Models\StaticPage::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $page->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                @endif
                <div class="col-md-9">
                    <label class="d-block">Tùy chọn</label>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="comments_enabled" value="1" id="comments_enabled" 
                               class="form-check-input" {{ old('comments_enabled', $page->comments_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="comments_enabled">Bật bình luận</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="is_pinned" value="1" id="is_pinned" 
                               class="form-check-input" {{ old('is_pinned', $page->is_pinned) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_pinned">Ghim trang</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="is_active" value="1" id="is_active" 
                               class="form-check-input" {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Hiển thị công khai</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> {{ $page->exists ? 'Cập nhật trang' : 'Lưu trang' }}
            </button>
            <a href="{{ route('admin.static-pages.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(function () {
    $('.js-editor').summernote({
        height: 260,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'hr']],
            ['view', ['codeview']]
        ]
    });
});
</script>
@endpush
@endsection
