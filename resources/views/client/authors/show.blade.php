@extends('layout.novelight')

@section('template_title', $author->name . ' books & novels')
@section('meta_description', $author->description ?: 'Read novels by ' . $author->name)

@section('content')
@php
    $authorInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($author->name, 0, 1));
@endphp

<main class="alpha-workspace alpha-author-page">
    <div class="container">
        <section class="alpha-author-card">
            <div class="alpha-author-avatar" aria-hidden="true">{{ $authorInitial }}</div>
            <div class="alpha-author-card__body">
                <small>Author</small>
                <h1>{{ $author->name }}</h1>

                <div class="alpha-author-stats">
                    <span><b>{{ number_format($stats['books']) }}</b> novels</span>
                    <span><b>{{ number_format($stats['views']) }}</b> reads</span>
                    <span><b>{{ number_format($stats['rating'], 1) }}</b> rating</span>
                </div>

                @if($author->description)
                    <div class="alpha-author-about">
                        <h2>About me</h2>
                        <p>{{ $author->description }}</p>
                    </div>
                @endif
            </div>
        </section>

        <div class="alpha-author-layout">
            <section>
                <h2 class="alpha-section-title">Novels by {{ $author->name }}</h2>

                <div class="alpha-story-list">
                    @forelse($articles as $article)
                        <article class="alpha-story-row">
                            <a href="{{ route_path('articles.show', $article) }}" class="alpha-story-row__poster">
                                <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                            </a>
                            <div>
                                <a href="{{ route_path('articles.show', $article) }}" class="alpha-story-row__title">{{ $article->title }}</a>
                                <div class="alpha-story-row__meta">
                                    @if($article->genres->isNotEmpty())
                                        <span>{{ $article->genres->pluck('name')->take(2)->join(', ') }}</span>
                                    @endif
                                    <span>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}</span>
                                    <span>{{ number_format($article->chapters_count ?? 0) }} chapters</span>
                                    <span><i class="fa fa-eye"></i> {{ number_format($article->view ?? 0) }}</span>
                                    @if(($article->rating_count ?? 0) > 0)
                                        <span><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</span>
                                    @endif
                                </div>
                                @if($article->description)
                                    <p class="alpha-author-book-desc clamp clamp-2">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($article->description), 150) }}
                                    </p>
                                @endif
                            </div>
                            <div class="alpha-story-row__actions">
                                <a href="{{ route_path('articles.show', $article) }}" class="alpha-btn alpha-btn--primary">Start Reading</a>
                            </div>
                        </article>
                    @empty
                        <div class="alpha-panel alpha-empty-state">
                            <i class="fa fa-book-open"></i>
                            No novels from this author yet.
                        </div>
                    @endforelse
                </div>

                <div class="alpha-pagination">
                    {{ $articles->links('vendor.pagination.novelight') }}
                </div>
            </section>

            <aside class="alpha-download-card">
                <div class="alpha-download-card__icon"><i class="fa fa-mobile-alt"></i></div>
                <h2>Alphanovel App</h2>
                <p>Read saved novels, follow author updates, and keep your library synced across devices.</p>
                <div class="alpha-download-card__actions">
                    <a href="{{ route_path('catalog.index') }}" class="alpha-btn alpha-btn--primary">Explore novels</a>
                    <a href="{{ route_path('pages.pricing') }}" class="alpha-btn">Get coins</a>
                </div>
            </aside>
        </div>
    </div>
</main>
@endsection
