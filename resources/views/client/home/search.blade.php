@extends('layout.novelight')

@section('template_title', $title ?? 'Search')
@section('meta_description', $description ?? 'Search novels')

@php
    $formatCompact = function ($value) {
        $value = (int) $value;
        if ($value >= 1000000) return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'M';
        if ($value >= 1000) return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'K';
        return number_format($value);
    };
@endphp

@section('content')
<main class="alpha-search-page">
    <section class="alpha-search-panel">
        <form action="{{ route_path('home.search') }}" method="GET" class="alpha-search-form">
            <input
                type="search"
                name="keyword"
                value="{{ $keyword }}"
                placeholder="Type novel title or tag..."
                autocomplete="off"
            >
            <button type="submit" aria-label="Search"><i class="fa fa-search"></i></button>
        </form>

        <h1>Top Tags</h1>
        <div class="alpha-search-tags">
            @forelse($topTags as $tag)
                <a href="{{ route_path('home.search', ['keyword' => $tag->name]) }}">{{ $tag->name }}</a>
            @empty
                <span>No tags yet.</span>
            @endforelse
        </div>
    </section>

    <section class="alpha-search-results">
        @forelse($articles as $article)
            @php
                $author = optional($article->authors->first())->name ?? 'Updating';
                $readUrl = route_path('articles.show', $article);
                $currentBookmark = $article->relationLoaded('bookmarks') ? $article->bookmarks->first() : null;
                $isFollowed = (bool) $currentBookmark;
            @endphp
            <article class="alpha-search-card">
                <a href="{{ route_path('articles.show', $article) }}" class="alpha-search-card__cover">
                    <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                    @if(($loop->index % 3) === 0)<em>Recommended</em>@endif
                    <strong>{{ $article->is_completed ? 'Completed' : 'Updated' }}</strong>
                </a>

                <div class="alpha-search-card__body">
                    <a href="{{ route_path('articles.show', $article) }}" class="alpha-search-card__title">
                        {{ $article->title }}
                    </a>
                    <div class="alpha-search-card__meta">
                        <span>Author: <b>{{ $author }}</b></span>
                        <span>Status: <b>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}</b></span>
                        <span>Age Rating: <b>{{ $article->is_adult ? '18+' : '16+' }}</b></span>
                    </div>
                    <div class="alpha-search-card__stats">
                        <span><i class="fa fa-eye"></i> {{ $formatCompact($article->view ?? 0) }}</span>
                        <span><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</span>
                    </div>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 270) }}</p>
                    <a href="{{ route_path('articles.show', $article) }}" class="alpha-search-more">more</a>
                </div>

                <div class="alpha-search-card__actions">
                    @auth
                        <form method="POST" action="{{ route_path('articles.bookmarks.store', $article->id) }}" class="alpha-search-bookmark-form">
                            @csrf
                            <input type="hidden" name="name" value="{{ $article->title }} #{{ $article->id }}">
                            <input type="hidden" name="status" value="{{ $isFollowed ? 'remove' : 'reading' }}">
                            <button type="submit" class="alpha-search-bookmark {{ $isFollowed ? 'is-followed' : '' }}" aria-label="{{ $isFollowed ? 'Remove from library' : 'Add to library' }}">
                                <i class="fa fa-heart"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route_path('login') }}" class="alpha-search-bookmark" aria-label="Bookmark">
                            <i class="fa fa-heart"></i>
                        </a>
                    @endauth
                    <a href="{{ $readUrl }}" class="alpha-search-start">Start Reading</a>
                </div>
            </article>
        @empty
            <div class="alpha-search-empty">
                No novels found.
            </div>
        @endforelse

        <div class="alpha-pagination">
            {{ $articles->links('vendor.pagination.novelight') }}
        </div>
    </section>
</main>
@endsection
