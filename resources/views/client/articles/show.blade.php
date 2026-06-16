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
    'url' => route('articles.show', $article),
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
    $bg = novel_bg($article);
    $firstChapter = $article->chapters()->orderBy('number')->first();
    $chapterCount = $article->chapters()->count();
@endphp

@section('content')
<div class="page-panel" style="background-image: url('{{ $bg }}');">
    <span class="background"></span>
</div>

<div class="container">
    <header class="header-manga">
        <div class="container">
            <h1 class="clamp clamp-2">{{ $article->title }}</h1>
        </div>
    </header>

    <div class="flex-content article-detail-flex">
        <main class="main block">
            <div class="section-select">
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
                        <a href="{{ route('genres.show', $genre) }}">{{ $genre->name }}</a>
                    @endforeach
                </section>
                @endif

                <section class="section text-info">
                    <h2>
                        {{ __('messages.article.latest_chapters') }}
                        <a href="#" class="meta-color header-small-text" section-target="chapters" id="show-all-chapters">{{ __('messages.article.view_all') }}</a>
                    </h2>
                    <div class="chapters">
                        @forelse($latestChapters as $chapter)
                            @php
                                $chapterCreditCost = $chapter->getEffectiveCreditCost($article);
                                $chapterIsPaid = $chapterCreditCost > 0;
                                $chapterIsUnlocked = $chapterIsPaid && (($unlockedChapterIds ?? collect())->contains($chapter->id) || ($hasActiveVip ?? false));
                            @endphp
                            <a href="{{ route('articles.chapters.show', [$article, $chapter->number]) }}" class="chapter ">
                                <div class="title">
                                    {{ __('messages.article.chapter') }} {{ $chapter->number }} - <span>{{ $chapter->title }}</span>
                                </div>
                                <div class="chapter-info">
                                    @if($chapterIsPaid)
                                        @guest
                                            <span class="cost"><i class="fa fa-lock"></i></span>
                                        @else
                                            @if($chapterIsUnlocked)
                                                <span class="cost paid">paid</span>
                                            @else
                                                <span class="cost"><i class="fa fa-money-bill"></i> {{ number_format($chapterCreditCost) }}</span>
                                            @endif
                                        @endguest
                                    @endif
                                    <span class="author"><i class="fa fa-eye"></i> {{ number_format($chapter->view) }}</span>
                                    <span class="date">{{ optional($chapter->created_at)->format('d.m.Y') }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="nothing">{{ __('messages.article.no_chapters') }}</div>
                        @endforelse
                    </div>
                </section>

                {{-- Similar — swiper 4 per view + arrows --}}
                @if(($suggestedArticles ?? collect())->count())
                <section class="manga-list section swp swp-single swp-4">
                    <h2 class="section-title">
                        <span>{{ __('messages.article.similar') }}</span>
                        <div class="arrows">
                            <div class="btn btn-invincible swiper-left"><i class="fa fa-chevron-left"></i></div>
                            <div class="btn btn-invincible swiper-right"><i class="fa fa-chevron-right"></i></div>
                        </div>
                    </h2>
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            @foreach($suggestedArticles as $s)
                                <div class="swiper-slide">
                                    <a href="{{ route('articles.show', $s) }}" class="manga-item">
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

                {{-- Đề xuất dịch (Translation requests) — swiper 4 per view + arrows --}}
                @if(($translationRequests ?? collect())->count())
                <section class="manga-list section swp swp-single swp-4">
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
                                    <a href="{{ route('articles.show', $s) }}" class="manga-item">
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
                <section class="section">
                    <h2 class="section-title">{{ __('messages.article.related_collections') }}</h2>
                    <div class="collections"><div class="collection-mini-grid">
                        @foreach($relatedGenres as $genre)
                            <a href="{{ route('genres.show', $genre) }}" class="collection-item">
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
                <section class="section comments-section">
                    <h2 class="section-title">
                        <span>{{ __('messages.article.latest_comments') }}</span>
                        <a href="#" id="show-all-comments" class="meta-color header-small-text" section-target="comments">{{ __('messages.article.view_all') }}</a>
                    </h2>
                    @forelse(collect($comments->items())->take(3) as $comment)
                        <div class="comment-preview">
                            <div class="comment-preview__main">
                                <div class="comment-block__header">
                                    <div class="left">
                                        <div class="comment-header__ava image image-cover lazy-load-bg">
                                            <img class="lazy-image" loading="eager" src="{{ optional($comment->user)->avatar ?: asset('static/account/images/no-ava.jpg') }}" alt="">
                                        </div>
                                        <div class="nickname">{{ optional($comment->user)->name ?? optional($comment->user)->username ?? __('messages.comments.anonymous') }}</div>
                                    </div>
                                    <div class="right"><div class="date meta-color">{{ optional($comment->created_at)->format('d.m.Y') }}</div></div>
                                </div>
                                <div class="text-info clamp clamp-3">{{ $comment->content }}</div>
                            </div>

                            @php $previewReplies = $comment->relationLoaded('replies') ? $comment->replies->take(2) : collect(); @endphp
                            @if($previewReplies->count())
                                <div class="comment-preview__replies">
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

                            <a href="#" class="comment-preview__reply-link comment-append-btn" data-open-reply="{{ $comment->id }}">
                                {{ __('messages.comments.reply') }}
                            </a>
                            <div class="comment-preview__append-to"></div>
                        </div>
                    @empty
                        <div class="nothing">{{ __('messages.article.no_comments') }}</div>
                    @endforelse
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
                    {{-- Chương hẹn giờ: hiện "Coming soon" để bạn đọc biết sắp ra, KHÔNG bấm đọc được. --}}
                    @foreach(($upcomingChapters ?? collect()) as $upcoming)
                        <div class="chapter chapter--coming-soon" aria-disabled="true">
                            <div class="title">
                                {{ __('messages.article.chapter') }} {{ $upcoming->number }} - <span>{{ $upcoming->title }}</span>
                            </div>
                            <div class="chapter-info">
                                <span class="cost coming-soon-badge"><i class="fa fa-clock"></i> Coming soon</span>
                                <span class="date">{{ optional($upcoming->published_at)->format('d.m.Y H:i') }}</span>
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
                         data-store-url="{{ route('articles.comments.store', $article->id) }}"
                         data-auth="{{ auth()->check() ? 1 : 0 }}">
                    <h2 class="section-title">{{ __('messages.comments.title') }} <span class="meta-color header-small-text">{{ number_format($comments->total()) }}</span></h2>

                    @auth
                        <form method="POST" action="{{ route('articles.comments.store', $article->id) }}" class="comments-form comments-form-view" id="main-comment-form">
                            @csrf
                            <textarea name="content" class="comments-form__text" placeholder="{{ __('messages.comments.placeholder') }}" required></textarea>
                            <div class="comments-form__actions">
                                <a href="{{ route('pages.rules') }}" class="btn btn-invincible" target="_blank">{{ __('messages.comments.rules') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('messages.comments.send') }}</button>
                            </div>
                        </form>
                    @else
                        <p class="meta-color login-to-comment">{!! __('messages.comments.login_prompt', ['login' => '<a href="'.route('login').'">'.__('messages.comments.login_link').'</a>']) !!}</p>
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
                <a href="{{ route('articles.chapters.show', [$article, $readChapterNumber]) }}" class="btn btn-primary read-btn">
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
                <a href="{{ route('catalog.index', ['type' => $article->novel_type]) }}" class="item">
                    <div class="sub-header">{{ __('messages.article.type') }}</div>
                    <div class="info">{{ $typeLabels[$article->novel_type] ?? 'Web Novel' }}</div>
                </a>
                @if(!empty($article->country))
                <a href="{{ route('catalog.index', ['country' => $article->country]) }}" class="item">
                    <div class="sub-header">{{ __('messages.article.country') }}</div>
                    <div class="info">{{ $countryLabels[$article->country] ?? 'Other' }}</div>
                </a>
                @endif
                <div class="item">
                    <div class="sub-header">{{ __('messages.article.release_year') }}</div>
                    <div class="info">{{ $article->year_of_release ?: optional($article->created_at)->format('Y') }}</div>
                </div>
                @if($article->authors->count())
                <a href="{{ route('authors.show', $article->authors->first()->id) }}" class="item">
                    <div class="sub-header">{{ __('messages.article.author') }}</div>
                    <div class="info">{{ $article->authors->first()->name }}</div>
                </a>
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
                            <a href="{{ route('genres.show', $genre) }}">{{ $genre->name }}</a>
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
    .article-detail-flex{flex-direction:column-reverse!important}
    .article-detail-flex .main{margin-right:0!important;width:100%!important;max-width:100%!important}
    .article-detail-flex .second-information{width:100%!important;max-width:100%!important}
    .article-detail-flex .second-information .poster{width:210px;height:290px;max-height:none;margin:0 auto 13px}

    /* Similar / Translation requests: bỏ slider -> lưới 2 cột, tên truyện ở dưới ảnh */
    .article-detail-flex .swp-single .arrows{display:none!important}
    .article-detail-flex .swp-single .swiper-container{overflow:visible!important}
    .article-detail-flex .swp-single .swiper-wrapper{
        display:grid!important;
        grid-template-columns:repeat(2,1fr)!important;
        gap:14px 12px!important;
        transform:none!important;
    }
    .article-detail-flex .swp-single .swiper-slide{
        width:auto!important;
        margin:0!important;
        height:auto!important;
    }
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
.swp-4 .manga-item{display:block;color:var(--text-color)}
.swp-4 .manga-item:visited{color:var(--text-color)}
.swp-4 .title{font-size:13px;line-height:1.3;font-weight:500}
</style>
@endpush

@push('scripts')
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
            window.location.href = "{{ route('login') }}";
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
        ajax("{{ route('articles.comments.store', $article->id) }}", {content: content, parent_id: parentId})
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
        ajax("{{ route('articles.comments.store', $article->id) }}", {content: content, parent_id: parentId})
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
        slidesPerView: 2, spaceBetween: 10, loop: false,
        navigation: { nextEl: section.querySelector('.swiper-right'), prevEl: section.querySelector('.swiper-left') },
        breakpoints: { 768: { slidesPerView: 4, spaceBetween: 12 }, 480: { slidesPerView: 3 } }
    });
});
</script>
@endpush
