@extends('layout.novelight')

@section('template_title', $selectedGenreName ? $selectedGenreName . ' Novels' : __('messages.catalog.page_title'))

@php
    $activeGenreId = count($selectedGenres ?? []) === 1 ? (int) $selectedGenres[0] : null;
    $pageTitle = $selectedGenreName ? $selectedGenreName . ' Novels' : __('messages.catalog.page_title');
    $pageDescription = $selectedGenreName
        ? 'Welcome ' . $selectedGenreName . ' Novels page on ' . config('app.name') . '! Enjoy a collection of ' . strtolower($selectedGenreName) . ' books with fresh chapters, popular stories, and reader favorites. Browse titles, compare status and ratings, and find your next story to read.'
        : 'Read novels online. Browse popular stories, fresh chapters, and completed books from every genre.';
@endphp

@section('content')
<main class="alpha-catalog-page">
    <aside class="alpha-catalog-sidebar">
        <section class="alpha-catalog-box">
            <h2>Genre</h2>
            <label class="alpha-genre-select" aria-label="Genre">
                <select onchange="if (this.value) window.location.href = this.value;">
                    <option value="{{ route_path('catalog.index') }}" {{ $activeGenreId ? '' : 'selected' }}>All Novels</option>
                    @foreach($genres as $genre)
                        <option value="{{ route_path('catalog.index', ['genre' => $genre->getRouteKey()]) }}" {{ $activeGenreId === (int) $genre->id ? 'selected' : '' }}>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <nav class="alpha-genre-list">
                <a href="{{ route_path('catalog.index') }}" class="{{ $activeGenreId ? '' : 'active' }}">All Novels</a>
                @foreach($genres as $genre)
                    <a href="{{ route_path('catalog.index', ['genre' => $genre->getRouteKey()]) }}" class="{{ $activeGenreId === (int) $genre->id ? 'active' : '' }}">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </nav>
        </section>
    </aside>

    <section class="alpha-catalog-main">
        <header class="alpha-catalog-intro">
            <h1>{{ $pageTitle }}</h1>
            <div class="alpha-catalog-description" data-collapsed="true">
                <p>{{ $pageDescription }}</p>
                <button type="button" class="alpha-catalog-description__toggle">more</button>
            </div>
            @if($articles->total() > 0)
                <span>{{ __('messages.catalog.found_results', ['count' => number_format($articles->total())]) }}</span>
            @endif
        </header>

        <div class="alpha-novel-list">
            @forelse($articles as $article)
                <a href="{{ route_path('articles.show', $article) }}" class="alpha-novel-card">
                    <span class="alpha-novel-card__cover">
                        <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                        @if($article->is_completed)<em>Completed</em>@endif
                    </span>
                    <span class="alpha-novel-card__body">
                        <strong class="clamp clamp-2">{{ $article->title }}</strong>
                        <small>
                            Author: {{ optional($article->authors->first())->name ?? 'Updating' }}
                            <span>Status: {{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}</span>
                        </small>
                        <span class="alpha-novel-card__stats">
                            <b><i class="fa fa-eye"></i> {{ number_format($article->view ?? 0) }}</b>
                            @if(($article->rating_count ?? 0) > 0)<b><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</b>@endif
                        </span>
                        <p class="clamp clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 170) }}</p>
                        <span class="alpha-more">more</span>
                    </span>
                </a>
            @empty
                <div class="alpha-empty">{{ __('messages.catalog.no_matching_results') }}</div>
            @endforelse
        </div>

        <div class="alpha-pagination">
            {{ $articles->links('vendor.pagination.novelight') }}
        </div>
    </section>
</main>

<script>
document.addEventListener('click', function (event) {
    var button = event.target.closest('.alpha-catalog-description__toggle');
    if (!button) return;
    var wrapper = button.closest('.alpha-catalog-description');
    if (!wrapper) return;
    var collapsed = wrapper.getAttribute('data-collapsed') !== 'false';
    wrapper.setAttribute('data-collapsed', collapsed ? 'false' : 'true');
    button.textContent = collapsed ? 'less' : 'more';
});
</script>
@endsection
