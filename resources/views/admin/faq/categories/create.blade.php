@extends('layout.admin')

@section('template_title', 'Create FAQ Category')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create FAQ Category</h3>
    </div>
    <form action="{{ route('admin.faq.categories.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" required>
                @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="title_en">Title (EN) *</label>
                <input type="text" class="form-control @error('title_en') is-invalid @enderror" id="title_en" name="title_en" value="{{ old('title_en') }}" required>
                @error('title_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="title_vi">Title (VI)</label>
                <input type="text" class="form-control @error('title_vi') is-invalid @enderror" id="title_vi" name="title_vi" value="{{ old('title_vi') }}">
                @error('title_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="description_en">Description (EN)</label>
                <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en" rows="3">{{ old('description_en') }}</textarea>
                @error('description_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="description_vi">Description (VI)</label>
                <textarea class="form-control @error('description_vi') is-invalid @enderror" id="description_vi" name="description_vi" rows="3">{{ old('description_vi') }}</textarea>
                @error('description_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="icon">Icon (Font Awesome class)</label>
                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', 'fa-question-circle') }}" placeholder="fa-question-circle">
                @error('icon')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                @error('sort_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Create Category</button>
            <a href="{{ route('admin.faq.categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
