@extends('layout.admin')

@section('template_title', 'Edit Forum Category')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Forum Category</h3>
    </div>
    <form action="/admin/forum/categories/{{ $category->id }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $category->slug) }}" required>
                @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="title_en">Title (EN) *</label>
                <input type="text" class="form-control @error('title_en') is-invalid @enderror" id="title_en" name="title_en" value="{{ old('title_en', $category->title_en) }}" required>
                @error('title_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="title_vi">Title (VI)</label>
                <input type="text" class="form-control @error('title_vi') is-invalid @enderror" id="title_vi" name="title_vi" value="{{ old('title_vi', $category->title_vi) }}">
                @error('title_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="description_en">Description (EN)</label>
                <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en" rows="3">{{ old('description_en', $category->description_en) }}</textarea>
                @error('description_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="description_vi">Description (VI)</label>
                <textarea class="form-control @error('description_vi') is-invalid @enderror" id="description_vi" name="description_vi" rows="3">{{ old('description_vi', $category->description_vi) }}</textarea>
                @error('description_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="section_label_en">Section Label (EN)</label>
                <input type="text" class="form-control @error('section_label_en') is-invalid @enderror" id="section_label_en" name="section_label_en" value="{{ old('section_label_en', $category->section_label_en) }}">
                @error('section_label_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="section_label_vi">Section Label (VI)</label>
                <input type="text" class="form-control @error('section_label_vi') is-invalid @enderror" id="section_label_vi" name="section_label_vi" value="{{ old('section_label_vi', $category->section_label_vi) }}">
                @error('section_label_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="icon">Icon (Font Awesome class)</label>
                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', $category->icon) }}">
                @error('icon')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                @error('sort_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Category</button>
            <a href="{{ route('admin.forum.categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
