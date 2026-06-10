@extends('layout.novelight')

@section('template_title', $post->localizedTitle() . ' - Forum')

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
@endsection

@section('content')
<div class="container">
    {{-- Breadcrumb --}}
    <div class="breadcumps">
        <a href="{{ route('pages.forum') }}" class="last-bread"><i class="fa fa-chevron-left"></i> Forum</a>
        <span>&gt;</span>
        <a href="{{ route('pages.forum.category', $category->slug) }}">{{ $category->localizedTitle() }}</a>
        <span>&gt;</span>
        <span>{{ $post->localizedTitle() }}</span>
    </div>

    @if($post->isPending())
        <div class="alert alert-warning">
            <i class="fa fa-clock"></i>
            {{ app()->getLocale() === 'vi'
                ? 'Bài viết đang chờ admin duyệt. Chỉ bạn mới thấy bài này.'
                : 'This post is pending approval. Only you can see it.' }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <article class="block forum-single">
        {{-- Post header: author left, stats right --}}
        <div class="forum-single-header">
            <div class="forum-single-header__author meta-color">
                @php $postAuthor = $post->author; @endphp
                <i class="fa fa-user"></i>
                @if($postAuthor)
                    <a href="{{ route('users.show.profile', $postAuthor) }}" class="forum-author-link">
                        {{ $postAuthor->name ?? $postAuthor->username }}
                    </a>,
                @else
                    {{ config('app.name') }},
                @endif
                {{ $post->created_at ? $post->created_at->format('d M Y - H:i:s') : '' }}
            </div>
            <div class="forum-single-header__stats meta-color">
                <span><i class="fa fa-eye"></i> {{ number_format($post->view_count ?? 0) }}</span>
                <span><i class="fa fa-comment"></i> {{ $post->comments()->count() }}</span>
            </div>
        </div>

        <h1 class="page-title">{{ $post->localizedTitle() }}</h1>
        <div class="text-info">{!! $post->localizedContent() !!}</div>

        {{-- Owner actions --}}
        @auth
            @if(auth()->id() === $post->user_id)
                <div class="forum-post-actions">
                    <a href="{{ route('forum.posts.edit', [$category->slug, $post->slug]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-edit"></i> {{ app()->getLocale() === 'vi' ? 'Sửa bài' : 'Edit' }}
                    </a>
                    <form method="POST" action="{{ route('forum.posts.destroy', [$category->slug, $post->slug]) }}"
                          class="d-inline"
                          onsubmit="return confirm('{{ app()->getLocale() === 'vi' ? 'Xoá bài này?' : 'Delete this post?' }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-trash"></i> {{ app()->getLocale() === 'vi' ? 'Xoá bài' : 'Delete' }}
                        </button>
                    </form>
                </div>
            @endif
        @endauth
    </article>

    @include('client.pages._static-page-comments', ['commentPage' => $post, 'comments' => $comments])
</div>
@endsection
