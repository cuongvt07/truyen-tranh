@extends('layout.novelight')

@section('template_title', $character->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($character->description ?? ''), 160) ?: $character->name)

@section('content')
@php
    $typeLabel = [
        __('messages.community.character_main'),
        __('messages.community.character_supporting'),
        __('messages.community.character_other'),
    ][$character->type] ?? __('messages.community.character_other');
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($character->name, 0, 1));
@endphp

<main class="alpha-workspace alpha-author-page">
    <div class="container">
        <section class="alpha-author-card">
            <div class="alpha-author-avatar alpha-character-avatar" aria-hidden="true">
                @if($character->photo)
                    <img src="{{ $character->photo }}" alt="{{ $character->name }}">
                @else
                    {{ $initial }}
                @endif
            </div>
            <div class="alpha-author-card__body">
                <small>{{ $typeLabel }}</small>
                <h1>{{ $character->name }}</h1>

                <div class="alpha-author-stats">
                    <span><b>{{ number_format($articles->total()) }}</b> novels</span>
                </div>

                @if(filled($character->description))
                    <div class="alpha-author-about">
                        <h2>Profile</h2>
                        <p>{{ $character->description }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section>
            <h2 class="alpha-section-title">{{ __('messages.community.appears_in_count', ['count' => $articles->total()]) }}</h2>

            <div class="alpha-story-list">
                @forelse($articles as $article)
                    <article class="alpha-story-row">
                        <a href="{{ route_path('articles.show', $article) }}" class="alpha-story-row__poster">
                            <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                        </a>
                        <div>
                            <a href="{{ route_path('articles.show', $article) }}" class="alpha-story-row__title">{{ $article->title }}</a>
                            <div class="alpha-story-row__meta">
                                @if($article->authors->isNotEmpty())
                                    <span>{{ $article->authors->pluck('name')->take(2)->join(', ') }}</span>
                                @endif
                                @if($article->genres->isNotEmpty())
                                    <span>{{ $article->genres->pluck('name')->take(2)->join(', ') }}</span>
                                @endif
                                <span>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}</span>
                                <span>{{ number_format($article->chapters_count ?? 0) }} chapters</span>
                            </div>
                            @if($article->description)
                                <p class="alpha-author-book-desc clamp clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 150) }}</p>
                            @endif
                        </div>
                        <div class="alpha-story-row__actions">
                            <a href="{{ route_path('articles.show', $article) }}" class="alpha-btn alpha-btn--primary">Read</a>
                        </div>
                    </article>
                @empty
                    <div class="alpha-panel alpha-empty-state">
                        <i class="fa fa-book-open"></i>
                        {{ __('messages.community.no_appearances') }}
                    </div>
                @endforelse
            </div>

            <div class="alpha-pagination">
                {{ $articles->links('vendor.pagination.novelight') }}
            </div>
        </section>
    </div>
</main>
@endsection
