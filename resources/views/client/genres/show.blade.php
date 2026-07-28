@extends('layout.novelight')

@section('template_title', $genre->name . ' Novels')
@section('meta_description', $genre->description ?: 'Read ' . $genre->name . ' novels online.')

@section('content')
<main class="alpha-catalog-page alpha-genre-page">
    <aside class="alpha-catalog-sidebar">
        <section class="alpha-catalog-box">
            <h2>Genre</h2>
            <label class="alpha-genre-select" aria-label="Genre">
                <select onchange="if (this.value) window.location.href = this.value;">
                    @foreach($genres as $item)
                        <option value="{{ route_path('genres.show', $item) }}" {{ (int) $item->id === (int) $genre->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <nav class="alpha-genre-list">
                @foreach($genres as $item)
                    <a href="{{ route_path('genres.show', $item) }}" class="{{ (int) $item->id === (int) $genre->id ? 'active' : '' }}">
                        {{ $item->name }}
                    </a>
                @endforeach
            </nav>
        </section>
    </aside>

    <section class="alpha-catalog-main">
        <header class="alpha-catalog-intro">
            <h1>{{ $genre->name }} Novels</h1>
            <div class="alpha-catalog-description" data-collapsed="true">
                <p>
                    {{ $genre->description ?: 'Read the best ' . strtolower($genre->name) . ' novels online. Browse popular stories, fresh updates, completed books, ratings, and chapters in one place.' }}
                </p>
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
                        @if($article->is_completed)<em>{{ __('messages.catalog.status_completed') }}</em>@endif
                    </span>
                    <span class="alpha-novel-card__body">
                        <strong class="clamp clamp-2">{{ $article->title }}</strong>
                        <small>
                            {{ optional($article->authors->first())->name ?? 'Updating' }}
                            <span>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}</span>
                            <span>{{ number_format($article->chapters_count ?? 0) }} chapters</span>
                        </small>
                        <span class="alpha-novel-card__stats">
                            <b><i class="fa fa-eye"></i> {{ number_format($article->view ?? 0) }}</b>
                            @if(($article->rating_count ?? 0) > 0)
                                <b><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</b>
                            @endif
                        </span>
                        <p class="clamp clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 140) }}</p>
                        <span class="alpha-more">more</span>
                    </span>
                </a>
            @empty
                <div class="alpha-empty">{{ __('messages.catalog.no_results_in_genre') }}</div>
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
