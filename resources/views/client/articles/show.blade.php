@extends('layout.novelight')

@section('template_title', $article->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($article->description), 160))
@section('og_type', 'book')
@section('og_image', novel_poster($article))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Book',
    'name' => $article->title,
    'url' => route_path('articles.show', $article),
    'description' => \Illuminate\Support\Str::limit(strip_tags($article->description), 250),
    'image' => novel_poster($article),
    'author' => ['@type' => 'Person', 'name' => optional($article->authors->first())->name ?? 'Updating'],
    'publisher' => ['@type' => 'Organization', 'name' => seo_setting('site_name', config('app.name')), 'url' => config('app.url')],
    'genre' => $article->genres->pluck('name')->all(),
    'inLanguage' => 'vi',
    'numberOfPages' => $article->chapters()->count(),
    'datePublished' => optional($article->created_at)->toAtomString(),
    'dateModified' => optional($article->updated_at)->toAtomString(),
    'aggregateRating' => ($article->rating ?? 0) > 0 ? [
        '@type' => 'AggregateRating',
        'ratingValue' => number_format($article->rating, 1),
        'ratingCount' => max(1, (int) ($article->rating_count ?? 1)),
        'bestRating' => '5', 'worstRating' => '1',
    ] : null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/book/css/singleee8b.css') }}?ver=1.8.0">
@endsection

@section('page_js')
<script src="{{ asset('static/book/js/singleee8b.js') }}?ver=1.8.0"></script>
@endsection

@php
    $poster = novel_poster($article);
    $firstChapter = $article->chapters()->orderBy('number')->first();
    $chapterCount = $article->chapters()->count();
    $primaryGenre = $article->genres->first();
    $primaryAuthor = $article->authors->first();
    $authorName = optional($primaryAuthor)->name ?? 'Updating';
    $readChapterNumber = $firstChapter ? ($continueChapterNumber ?: $firstChapter->number) : null;
    $descriptionPlain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $article->description)));
    $summaryLimit = 520;
    $summaryNeedsMore = \Illuminate\Support\Str::length($descriptionPlain) > $summaryLimit;
    $summaryPreview = $summaryNeedsMore
        ? \Illuminate\Support\Str::limit($descriptionPlain, $summaryLimit, '')
        : $descriptionPlain;
@endphp

@section('content')
<div class="container">
    <nav class="alpha-book-breadcrumb">
        <a href="{{ route_path('catalog.index') }}">Novels</a>
        @if($primaryGenre)
            <span>/</span>
            <a href="{{ route_path('genres.show', $primaryGenre) }}">{{ $primaryGenre->name }}</a>
        @endif
        <span>/</span>
        <span>{{ $article->title }}</span>
    </nav>

    <div class="flex-content article-detail-flex alpha-novel-detail">
        <main class="main block">
            <section class="alpha-book-detail-card">
                <div class="alpha-book-detail-cover lazy-load-bg">
                    <img class="lazy-image" loading="eager" src="{{ $poster }}" alt="{{ $article->title }} poster">
                </div>
                <div class="alpha-book-detail-card__main">
                    <h1 class="clamp clamp-2">{{ $article->title }}</h1>
                    <div class="alpha-book-detail-meta">
                        @if($primaryGenre)<span>Genre: <b>{{ $primaryGenre->name }}</b></span>@endif
                        <span>Author: <b>{{ $authorName }}</b></span>
                        <span>Chapters: <b>{{ number_format($chapterCount) }}</b></span>
                        <span>Status: <b>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}</b></span>
                        <span>Age Rating: <b>{{ ($article->adult ?? false) ? '18+' : '16+' }}</b></span>
                    </div>
                    <div class="alpha-book-detail-stats">
                        <span><i class="fa fa-eye"></i> {{ number_format($article->view ?? 0) }}</span>
                        <span><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</span>
                        <span><i class="fa fa-comment"></i> {{ number_format($comments->total()) }}</span>
                    </div>
                    <p class="alpha-book-description" data-summary>
                        <span data-summary-preview>{{ $summaryPreview }}</span><span data-summary-full hidden>{{ $descriptionPlain }}</span>@if($summaryNeedsMore) <button type="button" class="alpha-book-summary-more" data-summary-more>more...</button>@endif
                    </p>
                    @if($article->genres->count())
                        <div class="alpha-book-detail-tags">
                            @foreach($article->genres->take(10) as $genre)
                                <a href="{{ route_path('genres.show', $genre) }}">{{ $genre->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="alpha-book-detail-card__actions">
                    {{-- Dùng lại đúng khối bookmark của trang search: markup phẳng,
                         không có wrapper width:100% và <style> nội tuyến như
                         partials.add-to-list-button, nên không phải đè CSS. --}}
                    @auth
                        <form method="POST" action="{{ route_path('articles.bookmarks.store', $article->id) }}" class="alpha-search-bookmark-form">
                            @csrf
                            <input type="hidden" name="name" value="{{ $article->title }} #{{ $article->id }}">
                            <input type="hidden" name="status" value="{{ $currentListStatus ? 'remove' : 'reading' }}">
                            <button type="submit" class="alpha-search-bookmark {{ $currentListStatus ? 'is-followed' : '' }}"
                                    aria-label="{{ $currentListStatus ? 'Remove from library' : 'Add to library' }}"
                                    title="{{ $currentListStatus ? 'Remove from library' : 'Add to library' }}">
                                <i class="fa fa-heart"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route_path('login') }}" class="alpha-search-bookmark" aria-label="Bookmark" title="Add to library">
                            <i class="fa fa-heart"></i>
                        </a>
                    @endauth
                    <button type="button" class="alpha-share-button" aria-label="{{ __('messages.article.share') }}"
                            title="{{ __('messages.article.share') }}"
                            data-share-url="{{ route_path('articles.show', $article) }}"
                            data-share-title="{{ $article->title }}">
                        {{-- SVG nội tuyến: bộ FontAwesome của dự án là bản rút gọn,
                             không có .fa-share-square nên class đó vẽ ra glyph rác. --}}
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"></path>
                            <polyline points="16 6 12 2 8 6"></polyline>
                            <line x1="12" y1="2" x2="12" y2="15"></line>
                        </svg>
                    </button>
                </div>
            </section>

            <div class="section-select alpha-detail-tabs">
                <a href="#" class="active" section-target="information">{{ __('messages.article.tab_info') }}</a>
                <a href="#" section-target="chapters">{{ __('messages.article.tab_chapters') }}</a>
                <a href="#" section-target="comments">{{ __('messages.article.tab_comments') }}</a>
            </div>

            {{-- Information --}}
            <div class="main-section" id="information">
                <section class="text-info section">
                    {!! nl2br(e($article->description)) !!}
                </section>

                @if($article->genres->count())
                <section class="tags section">
                    @foreach($article->genres as $genre)
                        <a href="{{ route_path('genres.show', $genre) }}">{{ $genre->name }}</a>
                    @endforeach
                </section>
                @endif

                <section class="alpha-inline-reader section" id="inline-reader">
                    @if($firstChapter)
                        @php
                            $inlineChapter = $firstChapter;
                            $inlineCreditCost = $inlineChapter->getEffectiveCreditCost($article);
                            $inlineIsPaid = $inlineCreditCost > 0;
                            $inlineIsUnlocked = $inlineIsPaid && ($unlockedChapterIds ?? collect())->contains($inlineChapter->id);
                            $canReadInline = !$inlineIsPaid || $hasActiveVip || $inlineIsUnlocked;
                            $rawInlineContent = trim((string) $inlineChapter->content);
                            $rawInlineContent = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $rawInlineContent) ?? $rawInlineContent;
                            $inlineIsHtml = (bool) preg_match(
                                '#<(?:p|br|div|h[1-6]|ul|ol|li|blockquote|strong|em|b|i|u|a|img|figure|figcaption|span|table|tr|td|th|thead|tbody|hr|pre|code|sub|sup|mark)\b[^>]*>#i',
                                $rawInlineContent
                            );
                            $inlineBlocks = $inlineIsHtml
                                ? (preg_split('/(?<=<\/p>)/i', $rawInlineContent, -1, PREG_SPLIT_NO_EMPTY) ?: [$rawInlineContent])
                                : array_values(array_filter(preg_split('/(?:\r\n|\r|\n){2,}/', $rawInlineContent) ?: [], fn ($p) => trim($p) !== ''));
                            // Chương kế tiếp: trang chi tiết ĐÓNG VAI TRÒ chương 1 (giống alphanovel.io),
                            // nên cuối nội dung cần nút Next Chapter dẫn sang reader ?chapter=2.
                            $inlineNextChapter = $article->chapters()
                                ->where('number', '>', $inlineChapter->number)
                                ->orderBy('number')
                                ->first();
                        @endphp
                        <article class="alpha-inline-chapter" id="chapter-{{ $inlineChapter->number }}">
                            <header class="alpha-inline-chapter__header">
                                <h3>{{ $inlineChapter->title ?: __('messages.article.chapter') . ' ' . $inlineChapter->number }}</h3>
                                <a href="{{ route_path('articles.chapters.show', [$article, $inlineChapter->number]) }}" class="alpha-inline-chapter__legacy">Open</a>
                            </header>

                            @if($canReadInline)
                                <div class="alpha-inline-chapter__content">
                                    @forelse($inlineBlocks as $block)
                                        @if($inlineIsHtml){!! $block !!}@else<p>{!! nl2br(e($block)) !!}</p>@endif
                                    @empty
                                        <p>{{ __('messages.article.no_chapters') }}</p>
                                    @endforelse
                                </div>

                                @if($inlineNextChapter)
                                    <footer class="alpha-inline-chapter__footer">
                                        <a href="{{ route_path('articles.chapters.show', [$article, $inlineNextChapter->number]) }}"
                                           class="alpha-next-chapter alpha-next-chapter--primary">
                                            {{ __('messages.chapter.next_chapter') }}
                                        </a>
                                    </footer>
                                @endif
                            @else
                                @php
                                    $plainInline = trim(strip_tags($rawInlineContent));
                                    $teaser = \Illuminate\Support\Str::limit($plainInline, 360);
                                @endphp
                                <div class="alpha-inline-chapter__content alpha-inline-chapter__content--locked">
                                    <p>{{ $teaser }}</p>
                                    <div class="alpha-inline-lock">
                                        <strong><i class="fa fa-lock"></i> {{ __('messages.chapter.not_purchased') }}</strong>
                                        @auth
                                            <a href="{{ route_path('articles.chapters.show', [$article, $inlineChapter->number]) }}" class="alpha-next-chapter">
                                                {{ __('messages.chapter.buy_for', ['cost' => number_format($inlineCreditCost)]) }}
                                            </a>
                                        @else
                                            <a href="{{ route_path('login') }}" class="alpha-next-chapter">{{ __('messages.chapter.login_to_buy') }}</a>
                                        @endauth
                                    </div>
                                </div>
                            @endif
                        </article>
                    @else
                        <div class="nothing">{{ __('messages.article.no_chapters') }}</div>
                    @endif
                </section>

                <section class="alpha-latest-chapters section text-info">
                    <h2>
                        {{ __('messages.article.latest_chapters') }}
                        <a href="#" class="meta-color header-small-text" section-target="chapters" id="show-all-chapters">{{ __('messages.article.view_all') }}</a>
                    </h2>
                    <div class="chapters">
                        @forelse($latestChapters as $chapter)
                            @php
                                $chapterCreditCost = $chapter->getEffectiveCreditCost($article);
                                $chapterIsPaid = $chapterCreditCost > 0;
                                $chapterIsUnlocked = $chapterIsPaid && ($unlockedChapterIds ?? collect())->contains($chapter->id);
                            @endphp
                            <a href="{{ route_path('articles.chapters.show', [$article, $chapter->number]) }}" class="chapter ">
                                <div class="title">
                                    {{ __('messages.article.chapter') }} {{ $chapter->number }} - <span>{{ $chapter->title }}</span>
                                </div>
                                <div class="chapter-info">
                                    @if($chapterIsPaid)
                                        @guest
                                            <span class="cost"><i class="fa fa-lock"></i></span>
                                        @else
                                            @if($chapterIsUnlocked)
                                                <span class="cost paid">{{ __('messages.article.paid') }}</span>
                                            @else
                                                <span class="cost"><i class="fa fa-money-bill"></i> {{ number_format($chapterCreditCost) }}</span>
                                            @endif
                                        @endguest
                                    @endif
                                    <span class="author"><i class="fa fa-eye"></i> {{ number_format($chapter->view) }}</span>
                                    <span class="date">{{ optional($chapter->published_at ?: $chapter->created_at)->format('d.m.Y') }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="nothing">{{ __('messages.article.no_chapters') }}</div>
                        @endforelse
                    </div>
                </section>

                {{-- Teams --}}
                @if($article->team_id && $article->team)
                <section class="section translators alpha-detail-legacy">
                    <h2>{{ __('messages.article.teams') }}</h2>
                    <div class="items">
                        <a href="{{ route_path('teams.show', $article->team->id) }}" class="translator">
                            <div class="image image-cover">
                                <img loading="lazy" src="{{ $article->team->photo ?: asset('static/core/images/no_cover.webp') }}" alt="{{ $article->team->name }}">
                            </div>
                            <div class="name">{{ $article->team->name }}</div>
                        </a>
                    </div>
                </section>
                @endif

                {{-- Similar — swiper 4 per view + arrows --}}
                @if(($suggestedArticles ?? collect())->count())
                <section class="section alpha-suggestions">
                    <h2 class="section-title">
                        <span>You will also like</span>
                        <div class="alpha-slider-actions">
                            <button type="button" class="alpha-slider-prev" aria-label="Previous"><i class="fa fa-chevron-left"></i></button>
                            <button type="button" class="alpha-slider-next" aria-label="Next"><i class="fa fa-chevron-right"></i></button>
                        </div>
                    </h2>
                    <div class="alpha-suggestion-grid alpha-slider swiper-container" data-alpha-slider="suggestions">
                        <div class="swiper-wrapper">
                        @foreach($suggestedArticles as $s)
                            <div class="swiper-slide">
                                <a href="{{ route_path('articles.show', $s) }}" class="alpha-suggestion-card">
                                    <span class="alpha-suggestion-card__cover">
                                        <img loading="lazy" src="{{ novel_poster($s) }}" alt="{{ $s->title }}">
                                        @if($loop->first)<em>Recommended</em>@endif
                                    </span>
                                    <strong class="clamp clamp-2">{{ $s->title }}</strong>
                                    <small>{{ optional($s->authors->first())->name ?? 'Updating' }}</small>
                                </a>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </section>
                @endif

                {{-- Đề xuất dịch (Translation requests) — swiper 4 per view + arrows --}}
                @if(($translationRequests ?? collect())->count())
                <section class="manga-list section swp swp-single swp-4 alpha-translation-requests">
                    <h2 class="section-title">
                        <span>{{ __('messages.article.translation_requests') }}</span>
                        <div class="arrows">
                            <div class="btn btn-invincible swiper-left"><i class="fa fa-chevron-left"></i></div>
                            <div class="btn btn-invincible swiper-right"><i class="fa fa-chevron-right"></i></div>
                        </div>
                    </h2>
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            @foreach($translationRequests as $s)
                                <div class="swiper-slide">
                                    <a href="{{ route_path('articles.show', $s) }}" class="manga-item">
                                        <div class="poster image image-cover lazy-load-bg">
                                            <img class="lazy-image" loading="eager" src="{{ novel_poster($s) }}" alt="{{ $s->title }}">
                                        </div>
                                        <div class="title clamp clamp-2">{{ $s->title }}</div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
                @endif

                {{-- Related Collections --}}
                @if(($relatedGenres ?? collect())->count())
                <section class="section alpha-related-collections">
                    <h2 class="section-title">{{ __('messages.article.related_collections') }}</h2>
                    <div class="collections"><div class="collection-mini-grid related-collections-6">
                        @foreach($relatedGenres as $genre)
                            <a href="{{ route_path('genres.show', $genre) }}" class="collection-item">
                                <div class="collection__inner">
                                    <div class="collection-name clamp clamp-1">{{ $genre->name }}</div>
                                    <div class="collection-author meta-color clamp clamp-1">
                                        <i class="fa fa-book"></i> {{ __('messages.ui.article_count', ['count' => $genre->articles_count ?? 0]) }}
                                    </div>
                                    <div class="collection-meta__books"></div>
                                </div>
                            </a>
                        @endforeach
                    </div></div>
                </section>
                @endif

                {{-- Last Comments (preview) --}}
                <section class="section comments-section alpha-reviews-preview">
                    @php
                        $reviewItems = collect($comments->items())->take(3);
                        $reviewStatusLabel = $article->is_completed ? 'Review after the novel completion' : 'Review after half of the novel';
                    @endphp
                    <div class="alpha-reviews-preview__header">
                        <h2 class="section-title">Reviews</h2>
                        <a href="{{ route_path('articles.reviews', $article) }}" id="show-all-comments" class="alpha-reviews-preview__see-all">See All</a>
                    </div>

                    <div class="alpha-reviews-preview__grid">
                        @forelse($reviewItems as $comment)
                            <article class="comment-preview alpha-review-card">
                                <header class="alpha-review-card__header">
                                    <div class="alpha-review-card__user">
                                        <div class="comment-header__ava image image-cover lazy-load-bg">
                                            <img class="lazy-image" loading="eager" src="{{ optional($comment->user)->avatar ?: '/static/core/images/alphanovel/review-avatar_1.png' }}" alt="{{ optional($comment->user)->name ?? optional($comment->user)->username }}">
                                        </div>
                                        <div>
                                            <strong>{{ optional($comment->user)->name ?? optional($comment->user)->username ?? __('messages.comments.anonymous') }}</strong>
                                            <span>{{ $reviewStatusLabel }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="alpha-review-card__report alpha-review-report-trigger" data-comment="{{ $comment->id }}" aria-label="Report review">
                                        <i class="fa fa-shield-alt"></i>
                                    </button>
                                </header>

                                <p class="alpha-review-card__content clamp clamp-4">{{ $comment->content }}</p>

                                @php $previewReplies = $comment->relationLoaded('replies') ? $comment->replies->take(2) : collect(); @endphp
                                @if($previewReplies->count())
                                    <div class="comment-preview__replies alpha-review-card__replies">
                                        @foreach($previewReplies as $reply)
                                            <div class="comment-preview__reply">
                                                <div class="comment-preview__reply-meta">
                                                    {{ optional($reply->user)->name ?? optional($reply->user)->username ?? __('messages.comments.anonymous') }}
                                                    <span class="meta-color">{{ optional($reply->created_at)->format('d.m.Y') }}</span>
                                                </div>
                                                <div class="text-info clamp clamp-2">{{ $reply->content }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="alpha-review-card__footer">
                                    <span>{{ optional($comment->created_at)->format('F j, Y') }}</span>
                                    <a href="{{ route_path('articles.reviews.show', [$article, $comment]) }}">more</a>
                                </div>
                                <a href="#" class="comment-preview__reply-link comment-append-btn alpha-review-card__reply" data-open-reply="{{ $comment->id }}">
                                    {{ __('messages.comments.reply') }}
                                </a>
                                <div class="comment-preview__append-to"></div>
                            </article>
                        @empty
                            <div class="alpha-reviews-empty">
                                <i class="fa fa-comment-dots"></i>
                                <strong>No reviews yet</strong>
                                <span>{{ __('messages.article.no_comments') }}</span>
                            </div>
                        @endforelse
                    </div>
                </section>

            </div>

            {{-- All chapters --}}
            <div class="main-section hide" id="chapters">
                <div class="control-btns">
                    <button type="button" id="chapter-sort-btn" class="btn btn-invincible"><i class="fa fa-sort"></i> {{ __('messages.article.sort') }}</button>

                    @if(count($chapterPages) > 1)
                        <div class="text-input checkbox-input select">
                            <div class="text-input__wrapper">
                                <select name="select-pagination-chapter" id="select-pagination-chapter">
                                    @foreach($chapterPages as $chapterPage)
                                        <option value="{{ $chapterPage['page'] }}">{{ $chapterPage['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <div id="all-chapters-list" class="chapters">
                    {{-- Scheduled chapters are visible for reference but cannot be opened early. --}}
                    @foreach(($upcomingChapters ?? collect()) as $upcoming)
                        <div class="chapter chapter--coming-soon" aria-disabled="true">
                            <div class="title">
                                {{ __('messages.article.chapter') }} {{ $upcoming->number }} - <span>{{ $upcoming->title }}</span>
                            </div>
                            <div class="chapter-info">
                                <span class="cost coming-soon-badge">
                                    <i class="fa fa-clock" title="{{ __('messages.article.coming_soon') }}" aria-label="{{ __('messages.article.coming_soon') }}"></i>
                                </span>
                                <span class="date">
                                    {{ $upcoming->published_at->isTomorrow()
                                        ? __('messages.article.tomorrow')
                                        : $upcoming->published_at->format('d/m') }}
                                </span>
                            </div>
                        </div>
                    @endforeach

                    @include('client.articles.partials.chapter-list-items', [
                        'chapters' => $chapters,
                        'article' => $article,
                        'unlockedChapterIds' => $unlockedChapterIds,
                        'hasActiveVip' => $hasActiveVip,
                    ])
                </div>
            </div>
            <script>
                window.BOOK_ID = {{ $article->id }};
                window.BOOKMARK_CH = null;
            </script>

            {{-- Comments --}}
            <div class="main-section hide" id="comments">
                <section class="section comments comments-section" id="comments-section"
                         data-article="{{ $article->id }}"
                         data-store-url="{{ route_path('articles.comments.store', $article->id) }}"
                         data-auth="{{ auth()->check() ? 1 : 0 }}">
                    <h2 class="section-title">{{ __('messages.comments.title') }} <span class="meta-color header-small-text">{{ number_format($comments->total()) }}</span></h2>

                    @auth
                        <form method="POST" action="{{ route_path('articles.comments.store', $article->id) }}" class="comments-form comments-form-view" id="main-comment-form">
                            @csrf
                            <textarea name="content" class="comments-form__text" placeholder="{{ __('messages.comments.placeholder') }}" required></textarea>
                            <div class="comments-form__actions">
                                <a href="{{ route_path('pages.rules') }}" class="btn btn-invincible" target="_blank">{{ __('messages.comments.rules') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('messages.comments.send') }}</button>
                            </div>
                        </form>
                    @else
                        <p class="meta-color login-to-comment">{!! __('messages.comments.login_prompt', ['login' => '<a href="'.route_path('login').'">'.__('messages.comments.login_link').'</a>']) !!}</p>
                    @endauth

                    <ul class="comments main-comments" id="main-comments">
                        @forelse($comments as $comment)
                            @include('client.articles.partials.comment', ['comment' => $comment])
                        @empty
                            <li class="nothing no-comments" id="no-comments">{{ __('messages.comments.empty') }}</li>
                        @endforelse
                    </ul>
                    <div class="pagination-wrap">{{ $comments->withQueryString()->links() }}</div>
                </section>
            </div>
        </main>

        {{-- Sidebar --}}
        <div class="second-information">
            <div class="poster lazy-load-bg">
                <img class="lazy-image" loading="lazy" src="{{ $poster }}" alt="{{ $article->title }} poster">
            </div>

            @if($firstChapter)
                @php
                    $readChapterNumber = $continueChapterNumber ?: $firstChapter->number;
                @endphp
                <a href="{{ route_path('articles.chapters.show', [$article, $readChapterNumber]) }}" class="btn btn-primary read-btn">
                    {{ $hasStartedReading ? __('messages.article.continue_reading') : __('messages.article.read_from_start') }}
                </a>
            @endif

            @include('client.partials.add-to-list-button', [
                'article' => $article,
                'hasStartedReading' => $hasStartedReading,
                'currentListStatus' => $currentListStatus,
                'wantThisMode' => $wantThisMode,
                'interestCount' => $interestCount,
            ])

            <div class="block appreciate">
                @if(($article->rating_count ?? 0) > 0)
                    <div class="text"><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}/5</div>
                    <span>({{ number_format($article->rating_count ?? 0) }})</span>
                @endif
                @auth<div class="your">{{ __('messages.article.rate') }}</div>@endauth
            </div>

            @php
                $typeLabels = [0 => 'Web Novel', 1 => 'Light Novel', 2 => 'Published'];
                $countryLabels = [1 => 'China', 2 => 'Japan', 3 => 'Korea', 6 => 'Other'];
            @endphp
            <div class="block mini-info">
                <div class="item">
                    <div class="sub-header">{{ __('messages.article.status') }}</div>
                    <div class="info">{{ $article->is_completed ? __('messages.common.completed') : __('messages.common.ongoing') }}</div>
                </div>
                @if(!empty($article->alt_title))
                <div class="item">
                    <div class="sub-header">{{ __('messages.article.alt_name') }}</div>
                    <div class="info">{{ $article->alt_title }}</div>
                </div>
                @endif
                <a href="{{ route_path('catalog.index', ['type' => $article->novel_type]) }}" class="item">
                    <div class="sub-header">{{ __('messages.article.type') }}</div>
                    <div class="info">{{ $typeLabels[$article->novel_type] ?? 'Web Novel' }}</div>
                </a>
                @if(!empty($article->country))
                <a href="{{ route_path('catalog.index', ['country' => $article->country]) }}" class="item">
                    <div class="sub-header">{{ __('messages.article.country') }}</div>
                    <div class="info">{{ $countryLabels[$article->country] ?? 'Other' }}</div>
                </a>
                @endif
                <div class="item">
                    <div class="sub-header">{{ __('messages.article.release_year') }}</div>
                    <div class="info">{{ $article->year_of_release ?: optional($article->created_at)->format('Y') }}</div>
                </div>
                @if($primaryAuthor)
                    <a href="{{ route_path('authors.show', $primaryAuthor->id) }}" class="item">
                        <div class="sub-header">{{ __('messages.article.author') }}</div>
                        <div class="info">{{ $authorName }}</div>
                    </a>
                @else
                    <div class="item">
                        <div class="sub-header">{{ __('messages.article.author') }}</div>
                        <div class="info">{{ $authorName }}</div>
                    </div>
                @endif
                @if(!empty($article->illustrator))
                <div class="item">
                    <div class="sub-header">{{ __('messages.article.illustrator') }}</div>
                    <div class="info">{{ $article->illustrator }}</div>
                </div>
                @endif
                <div class="item">
                    <div class="sub-header">{{ __('messages.article.genres') }}</div>
                    <div class="info">
                        @foreach($article->genres as $genre)
                            <a href="{{ route_path('genres.show', $genre) }}">{{ $genre->name }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="item">
                    <div class="sub-header">{{ __('messages.article.chapters_count') }}</div>
                    <div class="info">{{ number_format($chapterCount) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Menu "..." cho bình luận (Remove / Report) --}}
<div class="comment-menu-dropdown" id="comment-menu" hidden>
    <a href="#" class="cm-item" data-action="remove" hidden><i class="fa fa-trash"></i> {{ __('messages.comments.remove') }}</a>
    <a href="#" class="cm-item" data-action="report"><i class="fa fa-flag"></i> {{ __('messages.comments.report') }}</a>
</div>

{{-- Report comment modal --}}
<div class="comment-report-overlay" id="report-overlay" hidden>
    <div class="comment-report-box">
        <h4>{{ __('messages.comments.report_title') }}</h4>
        <p class="meta-color">{{ __('messages.comments.report_desc') }}</p>
        <select id="report-reason" class="form-control">
            <option value="{{ __('messages.comments.reason_spam') }}">{{ __('messages.comments.reason_spam') }}</option>
            <option value="{{ __('messages.comments.reason_hate') }}">{{ __('messages.comments.reason_hate') }}</option>
            <option value="{{ __('messages.comments.reason_nsfw') }}">{{ __('messages.comments.reason_nsfw') }}</option>
            <option value="{{ __('messages.comments.reason_spoiler') }}">{{ __('messages.comments.reason_spoiler') }}</option>
            <option value="{{ __('messages.comments.reason_other') }}">{{ __('messages.comments.reason_other') }}</option>
        </select>
        <div class="report-actions">
            <button type="button" class="btn btn-invincible" id="report-cancel">{{ __('messages.comments.cancel') }}</button>
            <button type="button" class="btn btn-primary" id="report-submit">{{ __('messages.comments.report_submit') }}</button>
        </div>
    </div>
</div>

<div class="alpha-review-modal" id="alpha-review-report-modal" hidden>
    <div class="alpha-review-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="alpha-review-report-title">
        <button type="button" class="alpha-review-modal__close" aria-label="Close"><i class="fa fa-times"></i></button>
        <div class="alpha-review-modal__icon">!</div>
        <h2 id="alpha-review-report-title">Report an inappropriate review</h2>
        <p>Please describe exactly what you want to complain about in your feedback and include details so that we can process it faster. Thank you for your vigilance.</p>
        <textarea id="alpha-review-report-reason" placeholder="Please describe the reason why you are filing a complaint."></textarea>
        <div class="alpha-review-modal__actions">
            <button type="button" class="alpha-review-modal__cancel">Cancel</button>
            <button type="button" class="alpha-review-modal__submit" disabled>Send Request</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Chương hẹn giờ (Coming soon) trên tab chương: hiển thị nhưng không bấm đọc được */
.chapters .chapter--coming-soon{
    cursor:default;
    opacity:.72;
    pointer-events:none;
    background:repeating-linear-gradient(45deg,rgba(0,0,0,.015),rgba(0,0,0,.015) 8px,transparent 8px,transparent 16px);
}
.chapters .chapter--coming-soon .title span{font-style:italic;}
.coming-soon-badge{
    background:#f0ad4e;color:#fff;border-radius:4px;padding:1px 8px;font-size:12px;font-weight:600;white-space:nowrap;
}
.article-detail-flex .swp-single{
    display:flex;
    flex-direction:column;
}
.article-detail-flex .swp-single .section-title{
    display:flex!important;
    width:100%;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}
.article-detail-flex .swp-single .section-title > span{
    min-width:0;
}
.article-detail-flex .swp-single .section-title .arrows{
    display:flex;
    align-items:center;
    margin-left:auto;
    flex:0 0 auto;
}
.chapter-info .cost.paid{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:38px;
    padding:2px 7px;
    border-radius:4px;
    background:#1f9d55;
    color:#fff!important;
    font-size:12px;
    line-height:1.35;
    text-transform:uppercase;
    font-weight:700;
}
@media only screen and (max-width: 768px){
    .article-detail-flex{flex-direction:column!important;justify-content:flex-start!important}
    .article-detail-flex .main{margin-right:0!important;width:100%!important;max-width:100%!important}
    .article-detail-flex .second-information{width:100%!important;max-width:100%!important}
    .article-detail-flex .second-information .poster{width:210px;height:290px;max-height:none;margin:0 auto 13px}

    /* Similar / Translation requests: bỏ slider -> lưới 2 cột, tên truyện ở dưới ảnh */
    .article-detail-flex .swp-single .arrows{display:flex!important}
    .article-detail-flex .swp-single .swiper-slide .image{width:100%}
    .article-detail-flex .swp-single .swiper-slide .manga-list__info .title{
        white-space:normal;
        display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
    }
}

/* ===== Comments (novelight style) ===== */
.comments-section .section-title{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:14px}
.comments-form{margin-bottom:22px}
.comments-form__text{width:100%;min-height:90px;padding:12px 14px;border:1px solid var(--border,#d9dee7);border-radius:8px;background:var(--bg-input,#fff);color:inherit;resize:vertical;font:inherit;outline:none}
.comments-form__text:focus{border-color:var(--accent,#4f8ef7)}
.comments-form__actions{display:flex;justify-content:flex-end;gap:10px;margin-top:10px}
.login-to-comment{padding:14px;border:1px dashed var(--border,#d9dee7);border-radius:8px;text-align:center}

.comment-preview{padding:14px 0;border-bottom:1px solid var(--border,#eceef3)}
.comment-preview:last-child{border-bottom:none}
.comment-preview__main .comment-block__header{margin-bottom:8px}
.comment-preview__replies{margin:10px 0 0 28px;padding-left:14px;border-left:2px solid var(--border,#eceef3)}
.comment-preview__reply{padding:8px 0}
.comment-preview__reply-meta{font-size:13px;font-weight:600;margin-bottom:4px}
.comment-preview__reply-meta span{font-weight:400;margin-left:6px}
.comment-preview__reply-link{display:inline-block;margin-top:8px}
.comment-preview__append-to{margin-top:10px}

ul.comments,ul.comments-reply{list-style:none;margin:0;padding:0}
li.comment{padding:14px 0;border-bottom:1px solid var(--border,#eceef3)}
li.comment:last-child{border-bottom:none}
.comment-header{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.comment-header__ava{width:40px;height:40px;border-radius:50%;overflow:hidden;flex:0 0 40px;display:block}
.comment-header__ava img{width:100%;height:100%;object-fit:cover}
.comment-header__username{font-weight:600;color:inherit;text-decoration:none}
.comment-header__username:hover{color:var(--accent,#4f8ef7)}
.comment-header__meta{font-size:12px}
.comment-body .content{line-height:1.55;word-break:break-word;margin-bottom:6px}

.comment-controls{display:flex;align-items:center;justify-content:space-between;gap:12px}
.comment-controls .left{display:flex;align-items:center;gap:14px}
.comment-append-btn{font-size:13px;font-weight:600;color:var(--accent,#4f8ef7);text-decoration:none;cursor:pointer}
.comment-controls .additional{cursor:pointer;color:var(--text-muted,#8a93a5);letter-spacing:1px;font-weight:700;padding:0 4px;user-select:none}
.comment-controls .additional:hover{color:inherit}
.comment-vote{display:flex;align-items:center;gap:6px}
.comment-vote .btn{min-width:32px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;background:var(--bg-soft,#eceef3);cursor:pointer;font-size:13px;line-height:1;padding:0;border:1px solid var(--border,#dde1e9);color:#5b6472;transition:.15s}
.comment-vote .btn:hover{background:var(--bg-soft-hover,#dfe3ec);color:#2b303a}
.comment-vote .btn.like.active{background:rgba(46,160,67,.15);color:#2ea043}
.comment-vote .btn.dislike.active{background:rgba(248,81,73,.15);color:#f85149}
.comment-vote .btn.disabled{opacity:.4;cursor:not-allowed;pointer-events:none}
/* FA subset thiếu chevron-up/down → vẽ tam giác lên/xuống bằng CSS */
.comment-vote .like,.comment-vote .dislike{transform:none}
.comment-vote .fa-chevron-up,.comment-vote .fa-chevron-down{font-family:inherit}
.comment-vote .fa-chevron-up::before,.comment-vote .fa-chevron-down::before{content:"";display:inline-block;width:0;height:0;border:5px solid transparent}
.comment-vote .fa-chevron-up::before{border-bottom-color:currentColor;border-top:0}
.comment-vote .fa-chevron-down::before{border-top-color:currentColor;border-bottom:0}
.comment-vote .vote-score{min-width:18px;text-align:center;font-weight:600;font-size:13px}

.show-replies-btn{display:inline-block;margin-top:8px;font-size:13px;font-weight:600;text-decoration:none}
.comments-reply{margin-top:10px;padding-left:26px;border-left:2px solid var(--border,#eceef3)}
.comments-reply li.comment{padding:10px 0}

.reply-form{margin:10px 0 4px}
.reply-form textarea{width:100%;min-height:64px;padding:10px 12px;border:1px solid var(--border,#d9dee7);border-radius:8px;background:var(--bg-input,#fff);color:inherit;resize:vertical;font:inherit;outline:none}
.reply-form .reply-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:8px}

/* Dropdown "..." */
.comment-menu-dropdown{position:absolute;z-index:1200;min-width:140px;background:var(--bg-card,#fff);border:1px solid var(--border,#e0e4ec);border-radius:8px;box-shadow:0 8px 28px rgba(0,0,0,.16);padding:6px;display:flex;flex-direction:column}
.comment-menu-dropdown .cm-item{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:6px;color:inherit;text-decoration:none;font-size:14px}
.comment-menu-dropdown .cm-item:hover{background:var(--bg-soft,#f1f3f7)}
.comment-menu-dropdown .cm-item[data-action="remove"]{color:#f85149}

/* Modal báo cáo */
.comment-report-overlay{position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;padding:16px}
.comment-report-box{background:var(--bg-card,#fff);border-radius:12px;padding:22px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.comment-report-box h4{margin:0 0 6px}
.comment-report-box .form-control{width:100%;margin:12px 0;padding:10px;border:1px solid var(--border,#d9dee7);border-radius:8px;background:var(--bg-input,#fff);color:inherit}
.comment-report-box .report-actions{display:flex;justify-content:flex-end;gap:10px}
/* Similar / Translation requests — swiper 4 per view */
.swp-4 .poster{height:200px;margin-bottom:6px}
.swp-4 .manga-item{display:block;color:var(--text-color);text-decoration:none}
.swp-4 .manga-item:visited{color:var(--text-color)}
.swp-4 .title{font-size:13px;line-height:1.3;font-weight:500}
</style>
@endpush

@push('scripts')
<script>
// Nút chia sẻ: dùng hộp thoại chia sẻ của hệ điều hành nếu có (chủ yếu mobile),
// còn lại thì copy link vào clipboard và báo cho người dùng biết đã copy.
(function () {
    var btn = document.querySelector('.alpha-share-button');
    if (!btn) return;

    function toast(msg) {
        var el = document.createElement('div');
        el.className = 'alpha-share-toast';
        el.textContent = msg;
        document.body.appendChild(el);
        // Chờ 1 frame để transition chạy, nếu không nó hiện ngay không có hiệu ứng.
        requestAnimationFrame(function () { el.classList.add('is-on'); });
        setTimeout(function () {
            el.classList.remove('is-on');
            setTimeout(function () { el.remove(); }, 300);
        }, 2000);
    }

    btn.addEventListener('click', function () {
        var url = new URL(btn.dataset.shareUrl || location.pathname, location.origin).href;
        var title = btn.dataset.shareTitle || document.title;

        if (navigator.share) {
            navigator.share({ title: title, url: url }).catch(function () {});
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                toast(@json(__('messages.article.link_copied')));
            }).catch(function () {
                window.prompt(@json(__('messages.article.copy_link')), url);
            });
            return;
        }

        window.prompt(@json(__('messages.article.copy_link')), url);
    });
})();
</script>
<script>
(function () {
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-summary-more]');
        if (!button) return;

        var wrapper = button.closest('[data-summary]');
        if (!wrapper) return;

        var preview = wrapper.querySelector('[data-summary-preview]');
        var full = wrapper.querySelector('[data-summary-full]');
        if (!preview || !full) return;

        var expanded = button.getAttribute('data-expanded') === 'true';
        preview.hidden = !expanded;
        full.hidden = expanded;
        button.textContent = expanded ? 'more...' : 'less';
        button.setAttribute('data-expanded', expanded ? 'false' : 'true');
    });
})();
</script>
<script>
(function($){
    'use strict';
    var CSRF = $('meta[name="csrf-token"]').attr('content');
    var $section = $('#comments-section');
    if (!$section.length) return;
    var IS_AUTH = $section.data('auth') == 1;

    var T = {
        reply_placeholder: @json(__('messages.comments.reply_placeholder')),
        cancel:        @json(__('messages.comments.cancel')),
        send:          @json(__('messages.comments.send')),
        view_replies:  @json(__('messages.comments.view_replies')),
        hide_replies:  @json(__('messages.comments.hide_replies')),
        confirm_delete:@json(__('messages.comments.confirm_delete')),
        report_thanks: @json(__('messages.comments.report_thanks')),
        err_post:      @json(__('messages.comments.err_post')),
        err_reply:     @json(__('messages.comments.err_reply')),
        err_delete:    @json(__('messages.comments.err_delete')),
        err_report:    @json(__('messages.comments.err_report'))
    };
    var CURRENT_USER_NAME = @json(auth()->check() ? (auth()->user()->name ?? auth()->user()->username) : __('messages.comments.anonymous'));
    function viewReplies(n){ return '<i class="fa fa-comment-dots"></i> ' + T.view_replies.replace(':count', n); }
    function replyFormHtml(){
        return '<form class="reply-form">'
            + '<textarea placeholder="' + T.reply_placeholder + '" required></textarea>'
            + '<div class="reply-actions">'
            + '<button type="button" class="btn btn-invincible reply-cancel">' + T.cancel + '</button>'
            + '<button type="submit" class="btn btn-primary">' + T.send + '</button>'
            + '</div></form>';
    }

    function ajax(url, data){
        return $.ajax({
            url: url, method: 'POST', dataType: 'json',
            headers: {'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept':'application/json'},
            data: data || {}
        });
    }
    function needLogin(){
        if (!IS_AUTH){
            window.location.href = "{{ route_path('login') }}";
            return true;
        }
        return false;
    }

    function openArticleSection(target){
        $('.main-section').addClass('hide');
        $('#' + target).removeClass('hide');
        $('.section-select a').removeClass('active');
        $('.section-select a[section-target="' + target + '"]').addClass('active');
    }

    $('[data-open-reply]').on('click', function(e){
        e.preventDefault();

        var commentId = $(this).data('open-reply');
        var $holder = $(this).siblings('.comment-preview__append-to').first();

        if ($holder.children().length){ $holder.empty(); return; }
        $('.comment-preview__append-to, .comment-append-to').empty();
        $holder.html(replyFormHtml())
            .find('form')
            .attr('data-parent', commentId)
            .find('textarea')
            .focus();
    });

    /* ---------- VOTE ---------- */
    $section.on('click', '.comment-vote .btn', function(){
        var $btn = $(this);
        // Chưa upvote thì không cho bấm nút giảm.
        if ($btn.hasClass('dislike') && $btn.hasClass('disabled')) return;
        if (needLogin()) return;
        var $wrap = $btn.closest('.comment-vote');
        var id = $wrap.data('id');
        var val = $btn.data('vote');
        if ($btn.prop('disabled')) return;
        $wrap.find('.btn').prop('disabled', true);
        ajax("{{ url('comments') }}/" + id + "/vote", {value: val})
            .done(function(res){
                if (!res.ok) return;
                $wrap.find('.vote-score').text(res.score);
                $wrap.find('.like').toggleClass('active', res.myVote === 1);
                $wrap.find('.dislike').toggleClass('disabled', res.myVote !== 1);
            })
            .always(function(){ $wrap.find('.btn').prop('disabled', false); });
    });

    /* ---------- REPLY (toggle form) ---------- */
    $section.on('click', '.comment-append-btn', function(e){
        e.preventDefault();
        var id = $(this).data('reply');
        var $li = $('#comment-' + id);
        var $holder = $li.children('.comment-append-to').first();
        if ($holder.children().length){ $holder.empty(); return; }   // toggle close
        $('.comment-preview__append-to, .comment-append-to').empty();  // close other open forms
        $holder.html(replyFormHtml()).find('textarea').focus();
    });
    $(document).on('click', '.reply-cancel', function(){ $(this).closest('.comment-append-to, .comment-preview__append-to').empty(); });

    /* ---------- SUBMIT PREVIEW REPLY (ajax) ---------- */
    $(document).on('submit', '.comment-preview .reply-form', function(e){
        e.preventDefault();
        if (needLogin()) return;
        var $form = $(this);
        var parentId = $form.data('parent');
        var content = $form.find('textarea').val().trim();
        if (!content) return;
        var $submit = $form.find('[type="submit"]').prop('disabled', true);
        ajax("{{ route_path('articles.comments.store', $article->id) }}", {content: content, parent_id: parentId})
            .done(function(res){
                if (!res.ok) return;
                var $preview = $form.closest('.comment-preview');
                var $replies = $preview.children('.comment-preview__replies').first();
                if (!$replies.length) {
                    $replies = $('<div class="comment-preview__replies"></div>').insertBefore($preview.find('.comment-preview__reply-link').first());
                }
                $('<div class="comment-preview__reply"></div>')
                    .append($('<div class="comment-preview__reply-meta"></div>').text(CURRENT_USER_NAME))
                    .append($('<div class="text-info clamp clamp-2"></div>').text(content))
                    .appendTo($replies);
                $form.closest('.comment-preview__append-to').empty();
            })
            .fail(function(){ alert(T.err_reply); })
            .always(function(){ $submit.prop('disabled', false); });
    });

    /* ---------- SUBMIT REPLY (ajax) ---------- */
    $section.on('submit', '.reply-form', function(e){
        e.preventDefault();
        if (needLogin()) return;
        var $form = $(this);
        var $li = $form.closest('li.comment');
        var parentId = $li.data('id');
        var content = $form.find('textarea').val().trim();
        if (!content) return;
        var $submit = $form.find('[type="submit"]').prop('disabled', true);
        ajax("{{ route_path('articles.comments.store', $article->id) }}", {content: content, parent_id: parentId})
            .done(function(res){
                if (!res.ok) return;
                var $replyList = $li.children('.comments-reply').first();
                $replyList.append(res.html).prop('hidden', false);
                // refresh the "view replies" button label
                var $sr = $li.children('.show-replies-btn').first();
                var n = $replyList.children('li.comment').length;
                if ($sr.length){ $sr.html(viewReplies(n)); }
                $li.children('.comment-append-to').empty();
            })
            .fail(function(){ alert(T.err_reply); })
            .always(function(){ $submit.prop('disabled', false); });
    });

    /* ---------- SUBMIT MAIN COMMENT (ajax) ---------- */
    $('#main-comment-form').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        var content = $form.find('textarea').val().trim();
        if (!content) return;
        var $submit = $form.find('[type="submit"]').prop('disabled', true);
        ajax($form.attr('action'), {content: content})
            .done(function(res){
                if (!res.ok) return;
                $('#no-comments').remove();
                $('#main-comments').prepend(res.html);
                $form.find('textarea').val('');
            })
            .fail(function(){ alert(T.err_post); })
            .always(function(){ $submit.prop('disabled', false); });
    });

    /* ---------- SHOW / HIDE REPLIES ---------- */
    $section.on('click', '.show-replies-btn', function(e){
        e.preventDefault();
        var id = $(this).data('parent');
        var $list = $('#comment-' + id).children('.comments-reply').first();
        var wasHidden = $list.prop('hidden');
        $list.prop('hidden', !wasHidden);
        var n = $list.children('li.comment').length;
        // wasHidden=true => now visible => show "Hide"; else show "View N"
        $(this).html(wasHidden ? '<i class="fa fa-comment-dots"></i> ' + T.hide_replies : viewReplies(n));
    });

    /* ---------- MENU "..." (Remove / Report) ---------- */
    var $menu = $('#comment-menu');
    var menuTarget = null;
    $section.on('click', '.additional', function(e){
        e.preventDefault(); e.stopPropagation();
        menuTarget = $(this).data('menu');
        var canDelete = $(this).data('can-delete') == 1;
        $menu.find('[data-action="remove"]').prop('hidden', !canDelete);
        var off = $(this).offset();
        $menu.css({top: off.top + 22, left: off.left}).prop('hidden', false);
    });
    $(document).on('click', function(){ $menu.prop('hidden', true); });
    $menu.on('click', function(e){ e.stopPropagation(); });

    // Remove
    $menu.on('click', '[data-action="remove"]', function(e){
        e.preventDefault();
        $menu.prop('hidden', true);
        if (!menuTarget || !confirm(T.confirm_delete)) return;
        var id = menuTarget;
        $.ajax({
            url: "{{ url('articles') }}/{{ $article->id }}/comments/" + id,
            method: 'POST', dataType: 'json',
            headers: {'X-CSRF-TOKEN': CSRF, 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json'},
            data: {_method: 'DELETE'}
        }).done(function(res){
            if (res.ok){ $('#comment-' + id).slideUp(150, function(){ $(this).remove(); }); }
        }).fail(function(){ alert(T.err_delete); });
    });

    // Report -> mở modal
    var $overlay = $('#report-overlay');
    $menu.on('click', '[data-action="report"]', function(e){
        e.preventDefault();
        $menu.prop('hidden', true);
        if (needLogin()) return;
        $overlay.prop('hidden', false);
    });
    $('#report-cancel').on('click', function(){ $overlay.prop('hidden', true); });
    $overlay.on('click', function(e){ if (e.target === this) $overlay.prop('hidden', true); });
    $('#report-submit').on('click', function(){
        if (!menuTarget) return;
        var reason = $('#report-reason').val();
        var $b = $(this).prop('disabled', true);
        ajax("{{ url('comments') }}/" + menuTarget + "/report", {reason: reason})
            .done(function(res){ alert(res.message || T.report_thanks); })
            .fail(function(){ alert(T.err_report); })
            .always(function(){ $b.prop('disabled', false); $overlay.prop('hidden', true); });
    });

})(jQuery);
</script>
<script>
document.querySelectorAll('.swp-4 .swiper-container').forEach(function(el){
    var section = el.closest('.swp-4');
    new Swiper(el, {
        slidesPerView: 2, spaceBetween: 16, loop: false,
        navigation: { nextEl: section.querySelector('.swiper-right'), prevEl: section.querySelector('.swiper-left') },
        breakpoints: {
            768: { slidesPerView: 3, spaceBetween: 18 },
            1024: { slidesPerView: 5, spaceBetween: 20 }
        }
    });
});
</script>
<script>
(function () {
    var modal = document.getElementById('alpha-review-report-modal');
    if (!modal) return;
    var textarea = document.getElementById('alpha-review-report-reason');
    var submit = modal.querySelector('.alpha-review-modal__submit');
    var currentComment = null;

    function openModal(commentId) {
        currentComment = commentId;
        textarea.value = '';
        submit.disabled = true;
        modal.hidden = false;
        textarea.focus();
    }

    function closeModal() {
        modal.hidden = true;
        currentComment = null;
    }

    document.addEventListener('click', function (event) {
        var report = event.target.closest('.alpha-review-report-trigger');
        if (report) {
            event.preventDefault();
            openModal(report.getAttribute('data-comment'));
            return;
        }

        if (event.target === modal || event.target.closest('.alpha-review-modal__close') || event.target.closest('.alpha-review-modal__cancel')) {
            closeModal();
        }
    });

    textarea.addEventListener('input', function () {
        submit.disabled = textarea.value.trim().length < 3;
    });

    submit.addEventListener('click', function () {
        if (!currentComment || submit.disabled) return;
        submit.disabled = true;
        fetch('/comments/' + currentComment + '/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ reason: textarea.value.trim() })
        }).then(function (response) {
            if (response.status === 401) {
                window.location.href = @json(route_path('login', []));
                return null;
            }
            return response.json();
        }).then(function (data) {
            if (!data) return;
            alert(data.message || 'Your report has been sent.');
            closeModal();
        }).catch(function () {
            alert('Could not send the report. Please try again.');
            submit.disabled = false;
        });
    });
})();
</script>
@endpush
