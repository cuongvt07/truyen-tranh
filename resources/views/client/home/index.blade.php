@extends('layout.novelight')

@section('template_title', __('messages.nav.home'))

@php
    $firstGenre = function ($article) {
        return optional($article->genres->first())->name ?: 'Novel';
    };
@endphp

@section('content')
<main class="alpha-home alpha-discover">
    <section class="new-realeses alpha-new-realeses">
        <div class="alpha-discover-card__head">
            <h2>{{ __('messages.home.new_releases') }}</h2>
            <a href="{{ route_path('home.show_new_update_articles') }}" class="alpha-see-all">See All</a>
        </div>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                @forelse($newUpdateArticles as $article)
                    <div class="swiper-slide" style="background-image: url('{{ novel_poster($article) }}')">
                        <div class="background">
                            <a href="{{ route_path('articles.show', $article) }}" class="new-realeses__item">
                                <div class="left">
                                    <div class="poster">
                                        <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                    </div>
                                </div>
                                <div class="right">
                                    <div class="title clamp clamp-2">{{ $article->title }}</div>
                                    <div class="author">{{ optional($article->authors->first())->name ?? 'Updating' }}</div>
                                    <div class="excerpt clamp clamp-3">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($article->description ?: 'Read the latest release now.'), 190) }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="swiper-slide">
                        <div class="background">
                            <div class="new-realeses__item">
                                <div class="right">
                                    <div class="title">No novels yet.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @foreach($discoverBlocks as $block)
        <section class="alpha-discover-card alpha-discover-card--{{ $block['variant'] ?? 'rail' }}">
            <div class="alpha-discover-card__head">
                <h2>{{ $block['title'] }}</h2>
                <div class="alpha-slider-actions">
                    @if($block['showSeeAll'] ?? true)
                        <a href="{{ $block['url'] }}" class="alpha-see-all">See All</a>
                    @endif
                </div>
            </div>

            @if(($block['variant'] ?? 'rail') === 'trending')
                <div class="alpha-trending-grid alpha-slider swiper-container" data-alpha-slider="trending">
                    <div class="swiper-wrapper">
                    @forelse($block['articles'] as $article)
                        <div class="swiper-slide">
                            <a href="{{ route_path('articles.show', $article) }}" class="alpha-trending-item">
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
                        </div>
                    @empty
                        <div class="swiper-slide"><div class="alpha-empty">No novels yet.</div></div>
                    @endforelse
                    </div>
                </div>
            @else
                <div class="alpha-discover-rail alpha-slider swiper-container" data-alpha-slider="books">
                    <div class="swiper-wrapper">
                    @forelse($block['articles'] as $article)
                        <div class="swiper-slide">
                            <a href="{{ route_path('articles.show', $article) }}" class="alpha-discover-book">
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
                        </div>
                    @empty
                        <div class="swiper-slide"><div class="alpha-empty">No novels yet.</div></div>
                    @endforelse
                    </div>
                </div>
            @endif
        </section>
    @endforeach
</main>
@endsection
