@extends('layout.novelight')

@section('template_title', __('messages.myarticle.my_stories'))

@section('content')
<div class="alpha-workspace alpha-writer-page">
    <div class="container">
        <section class="alpha-workspace-hero">
            <div class="alpha-workspace-hero__row">
                <div>
                    <small>Writer center</small>
                    <h1>{{ __('messages.myarticle.my_stories') }}</h1>
                    <p>Manage submitted stories, add chapters, and keep your reader-facing pages ready.</p>
                </div>
                <a href="{{ route_path('my-articles.create') }}" class="alpha-btn alpha-btn--primary">
                    <i class="fa fa-plus"></i> {{ __('messages.myarticle.post_story') }}
                </a>
            </div>
        </section>

        @if(session('success'))
            <div class="alpha-alert alpha-alert--success">{{ session('success') }}</div>
        @endif

        @if($articles->isEmpty())
            <div class="alpha-panel alpha-empty-state">
                <i class="fa fa-book"></i>
                {{ __('messages.myarticle.no_stories') }}
                <a href="{{ route_path('my-articles.create') }}">{{ __('messages.myarticle.post_now') }}</a>
            </div>
        @else
            <div class="alpha-story-list">
                @foreach($articles as $article)
                    <article class="alpha-story-row">
                        <a href="{{ route_path('articles.show', $article) }}" class="alpha-story-row__poster">
                            <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                        </a>
                        <div>
                            <a href="{{ route_path('articles.show', $article) }}" class="alpha-story-row__title">{{ $article->title }}</a>
                            <div class="alpha-story-row__meta">
                                <span><i class="fa fa-book"></i> {{ __('messages.myarticle.chapters_count', ['count' => $article->chapters_count]) }}</span>
                                <span>{{ $article->is_completed ? __('messages.myarticle.status_completed') : __('messages.myarticle.status_ongoing') }}</span>
                                @if($article->status == \App\Enums\ArticleStatus::PENDING->value)
                                    <span style="color:#d89000">{{ __('messages.myarticle.status_pending') }}</span>
                                @endif
                                @if($article->team)
                                    <span><i class="fa fa-user-friends"></i> {{ $article->team->name }}</span>
                                @endif
                                <span><i class="fa fa-eye"></i> {{ number_format($article->view) }}</span>
                            </div>
                        </div>
                        <div class="alpha-story-row__actions">
                            <a href="{{ route_path('my-articles.create_chapter', $article->id) }}" class="alpha-btn" title="{{ __('messages.myarticle.add_chapter') }}">
                                <i class="fa fa-plus"></i> {{ __('messages.myarticle.chapter') }}
                            </a>
                            <a href="{{ route_path('my-articles.edit', $article->id) }}" class="alpha-btn" title="{{ __('messages.myarticle.edit') }}">
                                <i class="fa fa-edit"></i>
                            </a>
                            @if($article->can_delete ?? false)
                                <form method="post" action="{{ route_path('my-articles.destroy', $article->id) }}" onsubmit="return confirm('{{ __('messages.myarticle.delete_story_confirm') }}')">
                                    @csrf @method('delete')
                                    <button type="submit" class="alpha-btn alpha-btn--danger" title="{{ __('messages.myarticle.delete') }}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top:18px">{{ $articles->links('vendor.pagination.novelight') }}</div>
        @endif
    </div>
</div>
@endsection
