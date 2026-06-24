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
/* Card "Recently updated": badge NEW góc trên-trái + nhãn new cạnh ngày chương cuối */
.recently .manga-line-item .poster{ position:relative; overflow:hidden; }
.recently .manga-line-item .ribbon-new{
    position:absolute; top:6px; left:6px; z-index:2;
    background:#e3342f; color:#fff; font-size:10px; font-weight:700; line-height:1;
    padding:3px 6px; border-radius:4px; letter-spacing:.5px;
    box-shadow:0 1px 3px rgba(0,0,0,.35);
}
.recently .manga-line-item .last-chapter-date{ display:flex; align-items:center; gap:6px; }
.recently .manga-line-item .badge-new-inline{
    background:#e3342f; color:#fff; font-size:10px; font-weight:700; line-height:1;
    padding:2px 5px; border-radius:3px; text-transform:uppercase;
}
/* Thể loại: slider 1 hàng (override grid) */
.index-tags-swiper { display: block !important; }
.index-tags-swiper .swiper-container { overflow: hidden; }
.index-tags-swiper .swiper-slide { height: 100px; }
.index-tags-swiper .swiper-slide .tag {
    display: block; position: relative; width: 100%; height: 100px;
    background: #000; border-radius: 5px; overflow: hidden;
}
@media only screen and (max-width: 600px) {
    .index-tags-swiper .swiper-container {
        overflow: visible;
    }
    .index-tags-swiper .swiper-wrapper {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        transform: none !important;
    }
    .index-tags-swiper .swiper-slide {
        width: auto !important;
        height: 96px;
        margin-right: 0 !important;
    }
    .index-tags-swiper .swiper-slide:nth-child(n+7) {
        display: none;
    }
    .index-tags-swiper .swiper-slide .tag {
        height: 96px;
    }
}
</style>
@endpush

@section('content')
@php $navGenres = \App\Models\Genre::hot()->orderBy('name')->get(); @endphp {{-- danh sách thể loại home: chỉ thể loại Hot --}}
<div class="container">

    {{-- 1. POPULAR SWIPER --}}
    <section class="section">
        <h2>{{ __('messages.home.popular') }}</h2>
        <div class="block popular">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($hotArticles as $article)
                        <div class="swiper-slide">
                            <a href="{{ route('articles.show', $article) }}" class="manga-item">
                                <div class="poster image image-cover lazy-load-bg">
                                    <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                                </div>
                                <span>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}@if(($article->rating_count ?? 0) > 0) • {{ number_format($article->rating ?? 0, 1) }}<i class="fa fa-star"></i>@endif</span>
                                <div class="title clamp clamp-2">{{ $article->title }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 2. NEW UPDATED --}}
    @if($newUpdateArticles->isNotEmpty())
    <section class="section">
        <h2>{{ __('messages.home.new_updated') }}</h2>
        <div class="block popular">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($newUpdateArticles->take(16) as $article)
                        <div class="swiper-slide">
                            <a href="{{ route('articles.show', $article) }}" class="manga-item">
                                <div class="poster image image-cover lazy-load-bg">
                                    <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                                </div>
                                <span>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}@if(($article->rating_count ?? 0) > 0) • {{ number_format($article->rating ?? 0, 1) }}<i class="fa fa-star"></i>@endif</span>
                                <div class="title clamp clamp-2">{{ $article->title }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- 3. INDEX-TAGS (genres) --}}
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
    @if(($navGenres ?? collect())->isNotEmpty())
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
                            <a href="{{ route('genres.show', $genre) }}" class="tag">
                                <div class="background" style="background-image: url('{{ $imgUrl }}');"></div>
                                <div class="title">{{ $genre->name }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. TRANSLATION REQUESTS — truyện do user gửi, đã admin duyệt --}}
    @if($userSubmittedArticles->isNotEmpty())
    <div class="section">
        <h2>{{ __('messages.home.translate_req') }}</h2>
        <div class="block translation-requests">
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($userSubmittedArticles as $article)
                        <div class="swiper-slide">
                            <a href="{{ route('articles.show', $article) }}" class="manga-item">
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
    @endif

    {{-- 4. I'M READING --}}
    <section class="section">
        <h2>{{ __('messages.home.reading') }}</h2>
        <div class="block popular reading">
            @if($readingHistory->isNotEmpty())
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        @foreach($readingHistory as $item)
                            @php $article = $item->article; @endphp
                            @if(!$article) @continue @endif
                            <div class="swiper-slide">
                                <a href="{{ $item->chapter ? route('articles.chapters.show', [$article, $item->chapter_number]) : route('articles.show', $article) }}" class="manga-item">
                                    <div class="poster image image-cover lazy-load-bg">
                                        <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                                    </div>
                                    @if($item->chapter)
                                        <span>Chapter {{ $item->chapter_number }}</span>
                                    @endif
                                    <div class="title clamp clamp-2">{{ $article->title }}</div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="nothing">{{ __('messages.ui.no_articles_in_list') }}</div>
            @endif
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
                                <a href="{{ route('articles.show', $article) }}" class="new-realeses__item no-link">
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
                    @php
                        // Ngày chương mới nhất (đã đăng). Fallback updated_at nếu chưa có chương.
                        $lastChapterAt = $article->chapters_max_created_at
                            ? \Illuminate\Support\Carbon::parse($article->chapters_max_created_at)
                            : $article->updated_at;
                        $isNew = $lastChapterAt && $lastChapterAt->gt(now()->subDays(3));
                    @endphp
                    <a href="{{ route('articles.show', $article) }}" class="manga-line-item">
                        <div class="poster image image-cover lazy-load-bg">
                            @if($isNew)<span class="ribbon-new" title="{{ __('messages.ui.new') }}">{{ mb_strtoupper(mb_substr(__('messages.ui.new'), 0, 1)) }}</span>@endif
                            <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                        </div>
                        <div class="info">
                            <div class="title clamp clamp-1">{{ $article->title }}</div>
                            <div class="tag">
                                @foreach($article->genres->take(2) as $g){{ $g->name }}@if(!$loop->last), @endif @endforeach
                            </div>
                            <div class="last-chapter-date">
                                {{ $lastChapterAt ? $lastChapterAt->format('d.m.Y') : '—' }}
                                @if($isNew)<span class="badge-new-inline">{{ __('messages.ui.new') }}</span>@endif
                            </div>
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
                    <a href="{{ route('articles.show', $article) }}" class="news-post">
                        <div class="title">{{ $article->title }}</div>
                        <div class="date"><i class="fa fa-eye"></i> {{ number_format($article->view) }}</div>
                    </a>
                @endforeach
            </div>

            {{-- Forum (fix cứng tạm) --}}
            {{-- Last collections (dùng genres nhóm 3 ảnh) --}}
            <h2>{{ __('messages.ui.last_collections') }}</h2>
            <div class="collections">
                <div class="collection-mini-grid">
                    @forelse($lastCollections as $collection)
                        <a href="#" class="collection-item">
                            <div class="collection__inner">
                                <div class="collection-name clamp clamp-1">{{ $collection->name }}</div>
                                <div class="collection-author meta-color clamp clamp-1">
                                    <i class="fa fa-user"></i> {{ optional($collection->user)->username ?? optional($collection->user)->name ?? 'Unknown' }}
                                </div>
                                <div class="collection-meta__items">
                                    <div><i class="fa fa-comment"></i> {{ number_format($collection->comments_count ?? 0) }}</div>
                                    <div><i class="fa fa-book"></i> {{ number_format($collection->articles_count ?? 0) }}</div>
                                </div>
                                <div class="collection-meta__books">
                                    @foreach($collection->articles->take(3) as $article)
                                        <div class="image image-cover lazy-load-bg">
                                            <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="nothing">{{ __('messages.account.collections_empty') }}</div>
                    @endforelse
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
                    <a href="{{ route('articles.show', $comment->article_slug ?? $comment->article_id) }}" class="link clamp clamp-1">
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
