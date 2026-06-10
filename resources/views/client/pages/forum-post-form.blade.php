@extends('layout.novelight')

@section('template_title', $post ? (app()->getLocale() === 'vi' ? 'Chỉnh sửa bài viết' : 'Edit Post') : (app()->getLocale() === 'vi' ? 'Đăng bài viết mới' : 'New Post'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="breadcumps">
        <a href="{{ route('pages.forum') }}">Forum</a>
        <span>&gt;</span>
        <a href="{{ route('pages.forum.category', $category->slug) }}">{{ $category->localizedTitle() }}</a>
        <span>&gt;</span>
        <span>{{ $post ? (app()->getLocale() === 'vi' ? 'Chỉnh sửa' : 'Edit') : (app()->getLocale() === 'vi' ? 'Bài viết mới' : 'New Post') }}</span>
    </div>

    <div class="block forum-post-form">
        <h1 class="page-title">
            {{ $post
                ? (app()->getLocale() === 'vi' ? 'Chỉnh sửa bài viết' : 'Edit Post')
                : (app()->getLocale() === 'vi' ? 'Đăng bài viết mới' : 'New Post') }}
        </h1>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $action = $post
                ? route('forum.posts.update', [$category->slug, $post->slug])
                : route('forum.posts.store', $category->slug);
        @endphp

        <form method="POST" action="{{ $action }}" class="forum-form">
            @csrf
            @if($post) @method('PATCH') @endif

            <div class="form-group">
                <label>{{ app()->getLocale() === 'vi' ? 'Tiêu đề' : 'Title' }}</label>
                <input
                    type="text"
                    name="title"
                    class="form-control"
                    required
                    maxlength="255"
                    value="{{ old('title', $post ? $post->localizedTitle() : '') }}"
                    placeholder="{{ app()->getLocale() === 'vi' ? 'Nhập tiêu đề bài viết...' : 'Enter post title...' }}"
                >
            </div>

            <div class="form-group">
                <label>{{ app()->getLocale() === 'vi' ? 'Nội dung' : 'Content' }}</label>
                <textarea
                    name="content"
                    class="form-control forum-textarea"
                    required
                    rows="14"
                    placeholder="{{ app()->getLocale() === 'vi' ? 'Viết nội dung bài viết...' : 'Write your post content...' }}"
                >{{ old('content', $post ? $post->localizedContent() : '') }}</textarea>
            </div>

            <div class="forum-form-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-paper-plane"></i>
                    {{ $post
                        ? (app()->getLocale() === 'vi' ? 'Cập nhật' : 'Update')
                        : (app()->getLocale() === 'vi' ? 'Gửi bài' : 'Submit Post') }}
                </button>
                <a href="{{ route('pages.forum.category', $category->slug) }}" class="btn btn-secondary">
                    {{ app()->getLocale() === 'vi' ? 'Huỷ' : 'Cancel' }}
                </a>
                @if($post)
                    <form method="POST" action="{{ route('forum.posts.destroy', [$category->slug, $post->slug]) }}" class="d-inline"
                          onsubmit="return confirm('{{ app()->getLocale() === 'vi' ? 'Xoá bài viết này?' : 'Delete this post?' }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger ml-2">
                            <i class="fa fa-trash"></i> {{ app()->getLocale() === 'vi' ? 'Xoá bài' : 'Delete' }}
                        </button>
                    </form>
                @endif
            </div>

            @if(!$post)
                <p class="meta-color mt-2" style="font-size:0.85em">
                    <i class="fa fa-info-circle"></i>
                    {{ app()->getLocale() === 'vi'
                        ? 'Bài viết sẽ được hiển thị sau khi admin duyệt.'
                        : 'Your post will be visible after admin approval.' }}
                </p>
            @else
                <p class="meta-color mt-2" style="font-size:0.85em">
                    <i class="fa fa-info-circle"></i>
                    {{ app()->getLocale() === 'vi'
                        ? 'Sau khi chỉnh sửa, bài viết sẽ chờ duyệt lại.'
                        : 'After editing, the post will require re-approval.' }}
                </p>
            @endif
        </form>
    </div>
</div>
@endsection
