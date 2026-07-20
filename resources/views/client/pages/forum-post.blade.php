@extends('layout.novelight')

@section('template_title', $post->localizedTitle() . ' - ' . __('messages.nav.forum'))

@section('content')
<div class="alpha-workspace alpha-forum-page">
    <div class="container">
        <div class="alpha-book-breadcrumb" style="margin-bottom:16px">
            <a href="{{ route_path('pages.forum') }}">{{ __('messages.nav.forum') }}</a>
            <span>/</span>
            <a href="{{ route_path('pages.forum.category', $category->slug) }}">{{ $category->localizedTitle() }}</a>
            <span>/</span>
            <span>{{ $post->localizedTitle() }}</span>
        </div>

        @if($post->isPending())
            <div class="alpha-alert alpha-alert--warning">
                <i class="fa fa-clock"></i> {{ __('messages.forum.pending_notice') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alpha-alert alpha-alert--success">{{ session('success') }}</div>
        @endif

        <article class="alpha-article-content">
            <div class="alpha-topic-row__meta">
                @php $postAuthor = $post->author; @endphp
                <span><i class="fa fa-user"></i>
                    @if($postAuthor)
                        <a href="{{ route_path('users.show.profile', $postAuthor) }}">{{ $postAuthor->name ?? $postAuthor->username }}</a>
                    @else
                        {{ config('app.name') }}
                    @endif
                </span>
                <span>{{ $post->created_at ? $post->created_at->format('d M Y - H:i') : '' }}</span>
                <span><i class="fa fa-eye"></i> {{ number_format($post->view_count ?? 0) }}</span>
                <span><i class="fa fa-comment"></i> {{ $post->comments()->count() }}</span>
            </div>

            <h1>{{ $post->localizedTitle() }}</h1>
            <div class="text-info">{!! $post->localizedContent() !!}</div>

            @auth
                @if(auth()->id() === $post->user_id)
                    <div class="alpha-form-actions" style="margin-top:22px">
                        <a href="{{ route_path('forum.posts.edit', [$category->slug, $post->slug]) }}" class="alpha-btn">
                            <i class="fa fa-edit"></i> {{ __('messages.forum.edit') }}
                        </a>
                        <form method="POST" action="{{ route_path('forum.posts.destroy', [$category->slug, $post->slug]) }}"
                              onsubmit="return confirm(@js(__('messages.forum.delete_confirm')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="alpha-btn alpha-btn--danger">
                                <i class="fa fa-trash"></i> {{ __('messages.forum.delete_post') }}
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
        </article>

        <div style="margin-top:22px">
            @include('client.pages._static-page-comments', ['commentPage' => $post, 'comments' => $comments])
        </div>
    </div>
</div>
@endsection
