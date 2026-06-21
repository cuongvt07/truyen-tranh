@extends('layout.novelight')

@section('template_title', $post ? __('messages.forum.edit_post') : __('messages.forum.new_post_title'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="breadcumps">
        <a href="{{ route('pages.forum') }}">{{ __('messages.nav.forum') }}</a>
        <span>&gt;</span>
        <a href="{{ route('pages.forum.category', $category->slug) }}">{{ $category->localizedTitle() }}</a>
        <span>&gt;</span>
        <span>{{ $post ? __('messages.forum.edit_short') : __('messages.forum.new_post_short') }}</span>
    </div>

    <div class="block forum-post-form">
        <h1 class="page-title">
            {{ $post ? __('messages.forum.edit_post') : __('messages.forum.new_post_title') }}
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
                <label>{{ __('messages.forum.title') }}</label>
                <input
                    type="text"
                    name="title"
                    class="form-control"
                    required
                    maxlength="255"
                    value="{{ old('title', $post ? $post->localizedTitle() : '') }}"
                    placeholder="{{ __('messages.forum.title_placeholder') }}"
                >
            </div>

            <div class="form-group">
                <label>{{ __('messages.forum.content') }}</label>
                <textarea
                    name="content"
                    class="form-control forum-textarea"
                    required
                    rows="14"
                    placeholder="{{ __('messages.forum.content_placeholder') }}"
                >{{ old('content', $post ? $post->localizedContent() : '') }}</textarea>
            </div>

            <div class="forum-form-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-paper-plane"></i>
                    {{ $post ? __('messages.forum.update') : __('messages.forum.submit_post') }}
                </button>
                <a href="{{ route('pages.forum.category', $category->slug) }}" class="btn btn-secondary">
                    {{ __('messages.forum.cancel') }}
                </a>
                @if($post)
                    <form method="POST" action="{{ route('forum.posts.destroy', [$category->slug, $post->slug]) }}" class="d-inline"
                          onsubmit="return confirm(@js(__('messages.forum.delete_post_confirm')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger ml-2">
                            <i class="fa fa-trash"></i> {{ __('messages.forum.delete_post') }}
                        </button>
                    </form>
                @endif
            </div>

            @if(!$post)
                <p class="meta-color mt-2" style="font-size:0.85em">
                    <i class="fa fa-info-circle"></i>
                    {{ __('messages.forum.create_pending_notice') }}
                </p>
            @else
                <p class="meta-color mt-2" style="font-size:0.85em">
                    <i class="fa fa-info-circle"></i>
                    {{ __('messages.forum.edit_pending_notice') }}
                </p>
            @endif
        </form>
    </div>
</div>
@endsection
