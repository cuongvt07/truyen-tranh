@extends('layout.novelight')

@section('template_title', __('messages.nav.home'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/core/css/indexee8b.css') }}?ver=1.8.0">
@endsection

@section('page_js')
<script src="{{ asset('static/core/js/indexee8b.js') }}?ver=1.8.0"></script>
@endsection

@push('styles')
<style>
/* Thể loại: slider 1 hàng (override grid) */
.index-tags-swiper { display: block !important; }
.index-tags-swiper .swiper-container { overflow: hidden; }
.index-tags-swiper .swiper-slide { height: 100px; }
.index-tags-swiper .swiper-slide .tag {
    display: block; position: relative; width: 100%; height: 100px;
    background: #000; border-radius: 5px; overflow: hidden;
}
</style>
@endpush

@section('content')
@php $navGenres = \App\Models\Genre::orderBy('name')->get(); @endphp
<div class="container">

    {{-- 1. POPULAR SWIPER --}}
    <section class="section">
        <h2>{{ __('messages.home.popular') }}</h2>
        <div class="block popular">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($hotArticles as $article)
                        <div class="swiper-slide">
                            <a href="{{ route('articles.show', $article->id) }}" class="manga-item">
                                <div class="poster image image-cover lazy-load-bg">
                                    <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                                </div>
                                <span>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }} • {{ number_format($article->rating ?? 0, 1) }}<i class="fa fa-star"></i></span>
                                <div class="title clamp clamp-2">{{ $article->title }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 2. INDEX-TAGS (genres) --}}
    @php
        // Map tên thể loại -> ảnh fix cứng (bổ sung sau khi có ảnh thật)
        $genreImgMap = [
            'Tình cảm'  => 'media/genres/romance.jpg',
            'Hài hước'  => 'media/genres/comedy.jpg',
            'Kinh dị'   => 'media/genres/horror.jpg',
            'Kiếm hiệp' => 'media/genres/action.jpg',
            'Thám hiểm' => 'media/genres/fantasy.jpg',
            'Xuyên không'=> 'media/genres/fantasy.jpg',
            'Trinh thám'=> 'media/genres/scifi.jpg',
            'Hồi ký'    => 'media/genres/romance.jpg',
            // English fallbacks
            'Fantasy'   => 'media/genres/fantasy.jpg',
            'Action'    => 'media/genres/action.jpg',
            'Romance'   => 'media/genres/romance.jpg',
            'Comedy'    => 'media/genres/comedy.jpg',
            'Sci-Fi'    => 'media/genres/scifi.jpg',
            'Horror'    => 'media/genres/horror.jpg',
        ];
    @endphp
    <div class="section">
        <div class="block index-tags index-tags-swiper">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach(($navGenres ?? collect()) as $genre)
                        @php
                            $img = $genre->cover_image
                                ?? ($genreImgMap[$genre->name] ?? null);
                            $imgUrl = $img ? asset($img) : asset('static/core/images/no_cover.webp');
                        @endphp
                        <div class="swiper-slide">
                            <a href="{{ route('genres.show', $genre->id) }}" class="tag">
                                <div class="background" style="background-image: url('{{ $imgUrl }}');"></div>
                                <div class="title">{{ $genre->name }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- 3. TRANSLATION REQUESTS SWIPER (dùng random articles) --}}
    <div class="section">
        <h2>{{ __('messages.home.translate_req') }}</h2>
        <div class="block translation-requests">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($randomArticles as $article)
                        <div class="swiper-slide">
                            <a href="{{ route('articles.show', $article->id) }}" class="manga-item">
                                <div class="poster image image-cover lazy-load-bg">
                                    <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                                </div>
                                <div class="title clamp clamp-2">{{ $article->title }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- 4. I'M READING (bookmarks) --}}
    <section class="section">
        <h2>{{ __('messages.home.reading') }}</h2>
        <div class="block reading">
            @forelse($myBookmarks as $article)
                <a href="{{ route('articles.show', $article->id) }}" class="manga-item">
                    <div class="poster image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                    </div>
                    <div class="title clamp clamp-2">{{ $article->title }}</div>
                </a>
            @empty
                <div class="nothing">{{ __('messages.ui.no_articles_in_list') }}</div>
            @endforelse
        </div>
    </section>

    {{-- 5. NEW RELEASES SWIPER --}}
    <section class="section">
        <h2>{{ __('messages.home.new_releases') }}</h2>
        <div class="new-realeses">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($newUpdateArticles->take(10) as $article)
                        <div class="swiper-slide" style="background-image: url('{{ novel_poster($article) }}');">
                            <div class="background">
                                <a href="{{ route('articles.show', $article->id) }}" class="new-realeses__item no-link">
                                    <div class="left">
                                        <div class="poster image image-cover lazy-load-bg">
                                            <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                                        </div>
                                    </div>
                                    <div class="right">
                                        <div class="title clamp clamp-2">{{ $article->title }}</div>
                                        <div class="author">{{ optional($article->authors->first())->name ?? '' }}</div>
                                        <div class="excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 200) }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 6. RECENTLY ADDED + SIDEBAR --}}
    <section class="section flex-content">
        <div class="main">
            <h2>{{ __('messages.home.recently') }}</h2>
            <div class="block recently">
                @foreach($newUpdateArticles as $article)
                    <a href="{{ route('articles.show', $article->id) }}" class="manga-line-item">
                        <div class="poster image image-cover lazy-load-bg">
                            <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                        </div>
                        <div class="info">
                            <div class="title clamp clamp-1">{{ $article->title }}</div>
                            <div class="tag">
                                @foreach($article->genres->take(2) as $g){{ $g->name }}@if(!$loop->last), @endif @endforeach
                            </div>
                            <div>{{ optional($article->updated_at)->format('d.m.Y') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Sidebar phải --}}
        <div class="second-information second-information__index">

            {{-- Discord banner --}}
            <a href="#" class="promo-banner" style="background-image: url('{{ asset('static/core/images/discord_background.webp') }}'); background-size: contain;">
                <div class="wrapper">
                    <div class="title">{{ config('app.name') }}</div>
                    <div class="description">{{ __('messages.ui.promo_tagline') }}</div>
                </div>
            </a>

            {{-- Hoàn thành (thay News) --}}
            <h2>{{ __('messages.ui.completed') }}</h2>
            <div class="block">
                @foreach($completedArticles as $article)
                    <a href="{{ route('articles.show', $article->id) }}" class="news-post">
                        <div class="title">{{ $article->title }}</div>
                        <div class="date"><i class="fa fa-eye"></i> {{ number_format($article->view) }}</div>
                    </a>
                @endforeach
            </div>

            {{-- Forum (fix cứng tạm) --}}
            <h2>Forum</h2>
            <div class="block">
                <a href="#" class="news-post">
                    <div class="title">{{ __('messages.ui.forum_intro') }}</div>
                    <div class="date">{{ now()->format('d.m.Y') }}</div>
                </a>
                <a href="#" class="news-post">
                    <div class="title">{{ __('messages.ui.forum_suggest') }}</div>
                    <div class="date">{{ now()->subDays(2)->format('d.m.Y') }}</div>
                </a>
                <a href="#" class="news-post">
                    <div class="title">{{ __('messages.ui.forum_report') }}</div>
                    <div class="date">{{ now()->subDays(5)->format('d.m.Y') }}</div>
                </a>
                <a href="#" class="news-post">
                    <div class="title">{{ __('messages.ui.forum_vip') }}</div>
                    <div class="date">{{ now()->subDays(7)->format('d.m.Y') }}</div>
                </a>
            </div>

            {{-- Last collections (dùng genres nhóm 3 ảnh) --}}
            <h2>{{ __('messages.ui.featured_genres') }}</h2>
            <div class="collections">
                <div class="collection-mini-grid">
                    @foreach(($navGenres ?? collect())->take(4) as $genre)
                        @php
                            $genreArts = $genre->articles()->inRandomOrder()->take(3)->get();
                        @endphp
                        <a href="{{ route('genres.show', $genre->id) }}" class="collection-item">
                            <div class="collection__inner">
                                <div class="collection-name clamp clamp-1">{{ $genre->name }}</div>
                                <div class="collection-author meta-color clamp clamp-1">
                                    <i class="fa fa-book"></i> {{ __('messages.ui.article_count', ['count' => $genre->articles()->count()]) }}
                                </div>
                                <div class="collection-meta__books">
                                    @foreach($genreArts as $ga)
                                        <div class="image image-cover lazy-load-bg">
                                            <img class="lazy-image" loading="eager" src="{{ novel_poster($ga) }}" alt="">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 7. LAST COMMENTS --}}
    <section class="section">
        <h2>{{ __('messages.home.last_comments') }}</h2>
        <div class="block comment-blocks">
            @forelse($lastComments as $comment)
                <div class="comment-block">
                    <div class="comment-block__header">
                        <div class="left">
                            <div class="comment-header__ava image image-cover lazy-load-bg">
                                <img class="lazy-image" loading="eager" src="{{ asset('static/account/images/no-ava.jpg') }}" alt="">
                            </div>
                            <div class="nickname">{{ $comment->user_name }}</div>
                        </div>
                        <div class="right">
                            <div class="date meta-color">{{ \Carbon\Carbon::parse($comment->created_at)->format('d.m.Y') }}</div>
                        </div>
                    </div>
                    <div class="text-info clamp clamp-3">{{ $comment->content }}</div>
                    <a href="{{ route('articles.show', $comment->article_id) }}" class="link clamp clamp-1">
                        <i class="fa fa-book"></i> {{ $comment->article_title }}
                    </a>
                </div>
            @empty
                <div class="nothing">{{ __('messages.ui.no_comments') }}</div>
            @endforelse
        </div>
    </section>

</div>
@endsection
