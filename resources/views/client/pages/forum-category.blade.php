@extends('layout.novelight')

@section('template_title', $category->localizedTitle() . ' - Forum')

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
@endsection

@section('content')
<div class="container">
    {{-- Category header --}}
    <div class="forum-section-name block forum-section-name--header">
        <span><i class="fa fa-comments"></i> {{ strtoupper($category->localizedTitle()) }}</span>
    </div>

    @if($category->localizedExcerpt())
        <div class="block text-info">{{ $category->localizedExcerpt() }}</div>
    @endif

    {{-- Breadcrumb + New Post --}}
    <div class="forum-toolbar block">
        <div class="breadcumps">
            <a href="{{ route('pages.forum') }}" class="last-bread"><i class="fa fa-chevron-left"></i> Forum</a>
            <span>&gt;</span>
            <span>{{ $category->localizedTitle() }}</span>
        </div>
        @auth
            <a href="{{ route('forum.posts.create', $category->slug) }}" class="btn btn-primary btn-sm">
                <i class="fa fa-pen"></i> {{ app()->getLocale() === 'vi' ? 'Đăng bài' : 'New Post' }}
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                {{ app()->getLocale() === 'vi' ? 'Đăng nhập để đăng bài' : 'Login to post' }}
            </a>
        @endauth
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="forum-post-list">
        @forelse($posts as $post)
            <div class="fp-row block">
                <div class="fp-row__main">
                    <div class="fp-row__title">
                        @if($post->is_pinned)
                            <i class="fa fa-thumbtack fp-pin" title="{{ app()->getLocale() === 'vi' ? 'Ghim' : 'Pinned' }}"></i>
                        @endif
                        <a href="{{ route('pages.forum.post', [$category->slug, $post->slug]) }}" class="fp-row__title-link">
                            {{ $post->localizedTitle() }}
                        </a>
                    </div>
                    <div class="fp-row__meta meta-color">
                        {{ app()->getLocale() === 'vi' ? 'bởi' : 'by' }}
                        @if($post->author)
                            <a href="{{ route('users.show.profile', $post->author) }}" class="fp-author">{{ $post->author->name ?? $post->author->username }}</a>,
                        @else
                            <span>{{ config('app.name') }},</span>
                        @endif
                        {{ $post->created_at ? $post->created_at->format('d M Y - H:i:s') : '' }}
                    </div>
                </div>
                <div class="fp-row__stats meta-color">
                    <span><i class="fa fa-eye"></i> {{ number_format($post->view_count ?? 0) }}</span>
                    <span><i class="fa fa-comment"></i> {{ $post->comments_count ?? 0 }}</span>
                    @auth
                        @if(auth()->id() === $post->user_id)
                            <a href="{{ route('forum.posts.edit', [$category->slug, $post->slug]) }}"
                               class="fp-edit meta-color" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        @empty
            <div class="block meta-color" style="padding:16px">
                {{ app()->getLocale() === 'vi' ? 'Chưa có bài viết nào được duyệt.' : 'No approved posts yet.' }}
            </div>
        @endforelse
    </div>

    {{ $posts->links() }}
</div>
@endsection
