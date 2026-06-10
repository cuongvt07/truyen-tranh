@extends('layout.admin')

@section('template_title', 'Edit FAQ Article')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit FAQ Article</h3>
    </div>
    <form action="/admin/faq/articles/{{ $article->id }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="card-body">
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $article->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->title_en }}</option>
                    @endforeach
                </select>
                @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $article->slug) }}" required>
                @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="title_en">Title (EN) *</label>
                <input type="text" class="form-control @error('title_en') is-invalid @enderror" id="title_en" name="title_en" value="{{ old('title_en', $article->title_en) }}" required>
                @error('title_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="title_vi">Title (VI)</label>
                <input type="text" class="form-control @error('title_vi') is-invalid @enderror" id="title_vi" name="title_vi" value="{{ old('title_vi', $article->title_vi) }}">
                @error('title_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="content_en">Content (EN) *</label>
                <textarea class="form-control @error('content_en') is-invalid @enderror" id="content_en" name="content_en" rows="10" required>{{ old('content_en', $article->content_en) }}</textarea>
                @error('content_en')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="content_vi">Content (VI)</label>
                <textarea class="form-control @error('content_vi') is-invalid @enderror" id="content_vi" name="content_vi" rows="10">{{ old('content_vi', $article->content_vi) }}</textarea>
                @error('content_vi')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $article->sort_order) }}" min="0">
                @error('sort_order')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_pinned" name="is_pinned" value="1" {{ old('is_pinned', $article->is_pinned) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_pinned">Pinned</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="comments_enabled" name="comments_enabled" value="1" {{ old('comments_enabled', $article->comments_enabled) ? 'checked' : '' }}>
                <label class="form-check-label" for="comments_enabled">Comments Enabled</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $article->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Article</button>
            <a href="{{ route('admin.faq.articles.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
