@extends('layout.novelight')

@section('template_title', $title)
@section('meta_description', $description ?? $title)

@section('content')
<main class="alpha-list-page">
    <section class="alpha-catalog-main alpha-list-main">
        <header class="alpha-catalog-intro">
            <h1>{{ $title }}</h1>
            @if(!empty($description))
                <p>{{ $description }}</p>
            @endif
            @if($articles->total() > 0)
                <span>{{ __('messages.catalog.found_results', ['count' => number_format($articles->total())]) }}</span>
            @endif
        </header>

        <div class="alpha-novel-list">
            @forelse($articles as $article)
                @php
                    $newest = $article->relationLoaded('chapters') && $article->chapters->isNotEmpty()
                        ? $article->chapters->sortByDesc('number')->first()
                        : null;
                @endphp
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
                            @if($newest)<span>{{ __('messages.catalog.chapter', ['number' => $newest->number]) }}</span>@endif
                        </small>
                        <span class="alpha-novel-card__stats">
                            <b><i class="fa fa-eye"></i> {{ number_format($article->view ?? 0) }}</b>
                            @if(($article->rating_count ?? 0) > 0)
                                <b><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</b>
                            @endif
                        </span>
                        <p class="clamp clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 170) }}</p>
                        <span class="alpha-more">more</span>
                    </span>
                </a>
            @empty
                <div class="alpha-empty">{{ __('messages.catalog.no_results') }}</div>
            @endforelse
        </div>

        <div class="alpha-pagination">
            {{ $articles->links('vendor.pagination.novelight') }}
        </div>
    </section>
</main>
@endsection
