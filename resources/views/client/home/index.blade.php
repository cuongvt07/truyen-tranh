@extends('layout.novelight')

@section('template_title', __('messages.nav.home'))

@php
    $featuredArticle = $hotArticles->first() ?: $newUpdateArticles->first();
    $heroCover = $featuredArticle ? novel_poster($featuredArticle) : asset('static/core/images/no_cover.webp');

    $firstGenre = function ($article) {
        return optional($article->genres->first())->name ?: 'Novel';
    };
@endphp

@section('content')
<main class="alpha-home alpha-discover">
    <section class="alpha-app-hero">
        <div class="alpha-app-hero__media">
            <img src="{{ $heroCover }}" alt="{{ $featuredArticle ? $featuredArticle->title : config('app.name') }}" loading="eager">
        </div>
        <div class="alpha-app-hero__content">
            <h1>Use {{ config('app.name') }} to <span>read novels online</span> anytime and anywhere</h1>
            <p>
                Enter a world where you can read the stories and find the best romantic novel and fantasy books worthy of your attention.
            </p>
            <div class="alpha-app-hero__stores" aria-label="App stores">
                <span><i class="fa fa-play"></i></span>
                <span><i class="fab fa-apple"></i></span>
            </div>
        </div>
        <div class="alpha-app-hero__qr">
            <span>QR</span>
            <small>Scan the QR code, and go to the download app</small>
        </div>
    </section>

    @foreach($discoverBlocks as $block)
        <section class="alpha-discover-card alpha-discover-card--{{ $block['variant'] ?? 'rail' }}">
            <div class="alpha-discover-card__head">
                <h2>{{ $block['title'] }}</h2>
                @if($block['showSeeAll'] ?? true)
                    <a href="{{ $block['url'] }}" class="alpha-see-all">See All</a>
                @endif
            </div>

            @if(($block['variant'] ?? 'rail') === 'trending')
                <div class="alpha-trending-grid">
                    @forelse($block['articles'] as $article)
                        <a href="{{ route('articles.show', $article) }}" class="alpha-trending-item">
                            <span class="alpha-trending-item__cover">
                                <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                            </span>
                            <span class="alpha-trending-item__body">
                                <strong><em>{{ $loop->iteration }}.</em> <span class="clamp clamp-2">{{ $article->title }}</span></strong>
                                <small>{{ $firstGenre($article) }}</small>
                                <span class="alpha-trending-item__stats">
                                    <b><i class="fa fa-eye"></i> {{ number_format($article->view ?? 0) }}</b>
                                    @if(($article->rating_count ?? 0) > 0)
                                        <b><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</b>
                                    @endif
                                </span>
                            </span>
                        </a>
                    @empty
                        <div class="alpha-empty">No novels yet.</div>
                    @endforelse
                </div>
            @else
                <div class="alpha-discover-rail">
                    @forelse($block['articles'] as $article)
                        <a href="{{ route('articles.show', $article) }}" class="alpha-discover-book">
                            <span class="alpha-discover-book__cover">
                                <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                                @if($article->is_completed)
                                    <em>Completed</em>
                                @elseif(($article->chapters_count ?? 0) > 0)
                                    <em>Updated</em>
                                @endif
                            </span>
                            <strong class="clamp clamp-2">{{ $article->title }}</strong>
                        </a>
                    @empty
                        <div class="alpha-empty">No novels yet.</div>
                    @endforelse
                </div>
            @endif
        </section>
    @endforeach
</main>
@endsection
