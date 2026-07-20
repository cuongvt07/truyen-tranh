@extends('layout.novelight')

@section('template_title', $category->localizedTitle() . ' - ' . __('messages.nav.forum'))

@section('content')
<div class="alpha-workspace alpha-forum-page">
    <div class="container">
        <section class="alpha-workspace-hero alpha-workspace-hero--plain">
            <div class="alpha-workspace-hero__row">
                <div>
                    <small>{{ __('messages.nav.forum') }}</small>
                    <h1>{{ $category->localizedTitle() }}</h1>
                    @if($category->localizedExcerpt())<p>{{ $category->localizedExcerpt() }}</p>@endif
                </div>
                @auth
                    <a href="{{ route_path('forum.posts.create', $category->slug) }}" class="alpha-btn alpha-btn--primary">
                        <i class="fa fa-pen"></i> {{ __('messages.forum.new_post') }}
                    </a>
                @else
                    <a href="{{ route_path('login') }}" class="alpha-btn">{{ __('messages.forum.login_to_post') }}</a>
                @endauth
            </div>
        </section>

        @if(session('success'))
            <div class="alpha-alert alpha-alert--success">{{ session('success') }}</div>
        @endif

        <div class="alpha-book-breadcrumb" style="margin-bottom:14px">
            <a href="{{ route_path('pages.forum') }}">{{ __('messages.nav.forum') }}</a>
            <span>/</span>
            <span>{{ $category->localizedTitle() }}</span>
        </div>

        <div class="alpha-topic-list">
            @forelse($posts as $post)
                <article class="alpha-topic-row">
                    <div>
                        <a href="{{ route_path('pages.forum.post', [$category->slug, $post->slug]) }}">
                            @if($post->is_pinned)<i class="fa fa-thumbtack" style="color:#d89000"></i>@endif
                            {{ $post->localizedTitle() }}
                        </a>
                        <div class="alpha-topic-row__meta" style="margin-top:7px">
                            <span>{{ __('messages.forum.by') }}
                                @if($post->author)
                                    <a href="{{ route_path('users.show.profile', $post->author) }}">{{ $post->author->name ?? $post->author->username }}</a>
                                @else
                                    {{ config('app.name') }}
                                @endif
                            </span>
                            <span>{{ $post->created_at ? $post->created_at->format('d M Y - H:i') : '' }}</span>
                        </div>
                    </div>
                    <div class="alpha-topic-row__meta">
                        <span><i class="fa fa-eye"></i> {{ number_format($post->view_count ?? 0) }}</span>
                        <span><i class="fa fa-comment"></i> {{ $post->comments_count ?? 0 }}</span>
                        @auth
                            @if(auth()->id() === $post->user_id)
                                <a href="{{ route_path('forum.posts.edit', [$category->slug, $post->slug]) }}" title="{{ __('messages.forum.edit') }}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            @endif
                        @endauth
                    </div>
                </article>
            @empty
                <div class="alpha-panel alpha-empty-state">
                    <i class="fa fa-comments"></i>
                    {{ __('messages.forum.empty_approved_posts') }}
                </div>
            @endforelse
        </div>

        <div style="margin-top:18px">{{ $posts->links() }}</div>
    </div>
</div>
@endsection
