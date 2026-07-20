@extends('layout.novelight')

@section('template_title', $post ? __('messages.forum.edit_post') : __('messages.forum.new_post_title'))

@section('content')
@php
    $action = $post
        ? route_path('forum.posts.update', [$category->slug, $post->slug])
        : route_path('forum.posts.store', $category->slug);
@endphp

<div class="alpha-workspace alpha-forum-page">
    <div class="container">
        <section class="alpha-workspace-hero alpha-workspace-hero--plain">
            <small>{{ __('messages.nav.forum') }}</small>
            <h1>{{ $post ? __('messages.forum.edit_post') : __('messages.forum.new_post_title') }}</h1>
            <p>{{ $category->localizedTitle() }}</p>
        </section>

        <div class="alpha-book-breadcrumb" style="margin-bottom:16px">
            <a href="{{ route_path('pages.forum') }}">{{ __('messages.nav.forum') }}</a>
            <span>/</span>
            <a href="{{ route_path('pages.forum.category', $category->slug) }}">{{ $category->localizedTitle() }}</a>
        </div>

        <section class="alpha-panel alpha-panel--pad" style="max-width:880px;margin:0 auto">
            @if($errors->any())
                <div class="alpha-alert alpha-alert--danger">
                    <ul style="margin:0;padding-left:18px">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $action }}" class="alpha-form">
                @csrf
                @if($post) @method('PATCH') @endif

                <div class="alpha-field">
                    <label>{{ __('messages.forum.title') }}</label>
                    <input type="text" name="title" required maxlength="255" value="{{ old('title', $post ? $post->localizedTitle() : '') }}" placeholder="{{ __('messages.forum.title_placeholder') }}">
                </div>

                <div class="alpha-field">
                    <label>{{ __('messages.forum.content') }}</label>
                    <textarea name="content" required rows="14" placeholder="{{ __('messages.forum.content_placeholder') }}">{{ old('content', $post ? $post->localizedContent() : '') }}</textarea>
                </div>

                <div class="alpha-form-actions">
                    <button type="submit" class="alpha-btn alpha-btn--primary">
                        <i class="fa fa-paper-plane"></i>
                        {{ $post ? __('messages.forum.update') : __('messages.forum.submit_post') }}
                    </button>
                    <a href="{{ route_path('pages.forum.category', $category->slug) }}" class="alpha-btn">
                        {{ __('messages.forum.cancel') }}
                    </a>
                </div>

                <p class="meta-color" style="font-size:13px;margin:0">
                    <i class="fa fa-info-circle"></i>
                    {{ $post ? __('messages.forum.edit_pending_notice') : __('messages.forum.create_pending_notice') }}
                </p>
            </form>

            @if($post)
                <form method="POST" action="{{ route_path('forum.posts.destroy', [$category->slug, $post->slug]) }}"
                      onsubmit="return confirm(@js(__('messages.forum.delete_post_confirm')))"
                      style="margin-top:14px">
                    @csrf @method('DELETE')
                    <button type="submit" class="alpha-btn alpha-btn--danger">
                        <i class="fa fa-trash"></i> {{ __('messages.forum.delete_post') }}
                    </button>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
