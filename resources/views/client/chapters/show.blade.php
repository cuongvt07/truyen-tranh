@php
    $prevChapter = $article->chapters()->where('number', '<', $chapter->number)->orderByDesc('number')->first();
    $nextChapter = $article->chapters()->where('number', '>', $chapter->number)->orderBy('number')->first();
    $prevUrl = $prevChapter ? route('articles.chapters.show', [$article, $prevChapter->number]) : null;
    $nextUrl = $nextChapter ? route('articles.chapters.show', [$article, $nextChapter->number]) : null;
@endphp
<!doctype html>
<html lang="vi">
<head>
    <title>{{ __('messages.chapter.chapter') }} {{ $chapter->number }} - {{ $chapter->title }} · {{ $article->title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="{{ asset('static/favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Play:wght@400;700&family=Roboto:wght@100;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('static/core/css/reset.css') }}">
    <link rel="stylesheet" href="{{ asset('static/core/css/fontawesomeee8b.css') }}?ver=1.8.0">
    <link rel="stylesheet" href="{{ asset('static/core/css/styleee8b.css') }}?ver=1.8.0">
    <link rel="stylesheet" href="{{ asset('static/book/css/chapteree8b.css') }}?ver=1.8.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body chapter_ph="{{ (int) ($bookmarkParagraph ?? 0) }}">

<header class="header-chapter">
    <a href="{{ route('articles.show', $article) }}" class="header-title btn header-btn">
        <span class="clamp clamp-1"><i class="fa fa-arrow-left"></i> {{ $article->title }}</span>
    </a>
    <div class="control-btns">
        @auth
            <button type="button" id="bookmark-ph-btn" class="btn header-btn bookmark-paragraph"
                    title="{{ __('messages.chapter.bookmark_paragraph') }}"><i class="fa fa-bookmark"></i></button>
            <button type="button" id="report-chapter-btn" data-id="{{ $chapter->id }}" class="btn header-btn"
                    title="{{ __('messages.chapter.report_chapter') }}"><i class="fa fa-warning"></i></button>
        @endauth
        <button type="button" id="settings-open-btn" class="btn header-btn open-close"
                p-target="chapter-settings"><i class="fa fa-cog"></i></button>
    </div>
</header>

<div class="bookmark-ph-alert">{{ __('messages.chapter.select_paragraph_to_bookmark') }}</div>

<div class="chapter-control">
    @if($prevUrl)
        <a href="{{ $prevUrl }}" class="btn header-btn"><i class="fa fa-angle-left"></i></a>
    @else
        <span class="btn header-btn disabled"><i class="fa fa-angle-left"></i></span>
    @endif

    <div class="btn header-btn" id="table-of-contents-btn">
        <span>{{ __('messages.chapter.chapter') }} {{ $chapter->number }}</span>
        <span>{{ __('messages.chapter.table_of_contents') }}</span>
    </div>

    @if($nextUrl)
        <a href="{{ $nextUrl }}" class="btn header-btn"><i class="fa fa-angle-right"></i></a>
    @else
        <span class="btn header-btn disabled"><i class="fa fa-angle-right"></i></span>
    @endif
</div>

{{-- Chapter text --}}
<div class="chapter-text__place">
    <div class="chapter-title-block" style="text-align:center;padding:20px 0 10px">
        <h2>{{ __('messages.chapter.chapter') }} {{ $chapter->number }}: {{ $chapter->title }}</h2>
    </div>
    @php
        $rawContent = trim((string) $chapter->content);
        // Bỏ thẻ <script> để chống XSS, vẫn giữ thẻ định dạng.
        $rawContent = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $rawContent);
        $isHtmlContent = strip_tags($rawContent) !== $rawContent;
        if ($isHtmlContent) {
            // Nội dung HTML (CKEditor): GIỮ NGUYÊN thẻ; tách block sau mỗi </p> để chèn quảng cáo / cắt teaser.
            $chapterBlocks = preg_split('/(?<=<\/p>)/i', $rawContent, -1, PREG_SPLIT_NO_EMPTY) ?: [$rawContent];
        } else {
            // Plain text: tách đoạn theo dòng trống.
            $chapterBlocks = array_values(array_filter(
                preg_split('/(?:\r\n|\r|\n){2,}/', $rawContent) ?: [],
                fn ($p) => trim($p) !== ''
            ));
        }
        $isChapterLocked = $isLocked ?? false;
    @endphp

    @if($isChapterLocked)
        {{-- Chương trả phí: hiện 1/2 nội dung (theo số ký tự), ẩn 1/2 còn lại — KHÔNG render ra DOM (chống bypass).
             Chỉ đếm nội dung chương ($chapter->content), không tính khối cấu hình footer. --}}
        @php
            $teaserLimit = max(1, (int) ceil(mb_strlen(trim(strip_tags($rawContent))) * 0.5));
            $teaserHtml = '';
            $teaserAcc = 0;
            foreach ($chapterBlocks as $block) {
                if ($teaserAcc >= $teaserLimit) break;
                $plainLen = mb_strlen(trim(strip_tags($block)));
                if ($teaserAcc + $plainLen <= $teaserLimit) {
                    $teaserHtml .= $isHtmlContent ? $block : '<p>'.nl2br(e($block)).'</p>';
                    $teaserAcc += $plainLen;
                } else {
                    $snippet = mb_substr(trim(strip_tags($block)), 0, max(1, $teaserLimit - $teaserAcc));
                    $teaserHtml .= '<p>'.nl2br(e($snippet)).'…</p>';
                    break;
                }
            }
        @endphp
        <div class="chapter-text chapter-text__limit" id="chapter-c">
            {!! $teaserHtml !!}
        </div>

        {{-- Paywall --}}
        <div class="chapter-paywall">
            <p class="paywall-note">{{ __('messages.chapter.not_purchased') }}</p>
            @auth
                @if(($userPoints ?? 0) >= $creditCost)
                    <button type="button" id="btn-buy-chapter" class="btn btn-primary"
                            data-url="{{ route('articles.chapters.unlock', [$article, $chapter->number]) }}">
                        {{ __('messages.chapter.buy_for', ['cost' => number_format($creditCost)]) }}
                    </button>
                @else
                    <button type="button" class="btn" disabled>{{ __('messages.chapter.not_enough_credit') }}</button>
                    <div class="paywall-topup"><a href="{{ route('client.paypoints') }}">{{ __('messages.chapter.topup_now') }}</a></div>
                @endif
                <div id="buy-msg"></div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">{{ __('messages.chapter.login_to_buy') }}</a>
            @endauth
        </div>
    @else
        <div class="chapter-text" id="chapter-c">
            @php
                $inlineAds = collect($inlineChapterAds ?? []);
                $firstInlineAdAfter = max(1, (int) ($inlineAdFirstAfter ?? 4));
                $inlineAdEvery = max(1, (int) ($inlineAdEvery ?? 8));
                $inlineAdSlot = 0;
            @endphp

            @foreach($chapterBlocks as $blockIndex => $block)
                @if($isHtmlContent){!! $block !!}@else<p>{!! nl2br(e($block)) !!}</p>@endif

                @php
                    $blockNumber = $blockIndex + 1;
                    $shouldShowInlineAd = $inlineAds->isNotEmpty()
                        && $inlineAdSlot < $inlineAds->count()
                        && $blockNumber >= $firstInlineAdAfter
                        && (($blockNumber - $firstInlineAdAfter) % $inlineAdEvery === 0);
                @endphp

                @if($shouldShowInlineAd)
                    @php
                        $inlineAd = $inlineAds[$inlineAdSlot];
                        $inlineAdSlot++;
                    @endphp
                    @include('client.partials.chapter-inline-ad', ['ad' => $inlineAd])
                @endif
            @endforeach
        </div>
    @endif
</div>

<div class="chapter-team__info">
    <div class="chapter-info-end">
        @php
            $cfText1 = setting('chapter_footer_text1');
            $cfText2 = setting('chapter_footer_text2');
            $cfImage = setting('chapter_footer_image');
            $cfLink  = setting('chapter_footer_link');
            $hasChapterFooter = $cfText1 || $cfText2 || $cfImage;
        @endphp
        <div class="chapter_team">
            @if($hasChapterFooter)
                <a class="team" @if($cfLink) href="{{ $cfLink }}" target="_blank" rel="noopener" @endif>
                    @if($cfImage)
                        <div class="image image-cover lazy-load-bg">
                            <img loading="lazy" class="lazy-image" src="{{ asset('storage/' . $cfImage) }}" alt="{{ $cfText2 ?: $cfText1 }}">
                        </div>
                    @endif
                    <div class="text">
                        @if($cfText1)<span>{{ $cfText1 }}</span>@endif
                        @if($cfText2)<div class="name">{{ $cfText2 }}</div>@endif
                    </div>
                </a>
            @else
                <div class="text">
                    <span>{{ __('messages.chapter.posted_by') }}</span>
                    <div class="name">{{ optional($article->user)->name ?? config('app.name') }}</div>
                </div>
            @endif
        </div>
        <div class="buttons">
            @auth
                <button type="button" id="btn-like-chapter" class="btn btn-invincible {{ ($userLikedChapter ?? false) ? 'liked' : '' }}"
                        data-url="{{ route('articles.chapters.like', [$article, $chapter->number]) }}">
                    <i class="fa fa-heart"></i> {{ __('messages.chapter.give_thanks') }} | <span id="likes-count">{{ (int) ($chapterLikesCount ?? 0) }}</span>
                </button>
                <button type="button" id="btn-bookmark-chapter" class="btn btn-invincible bookmark {{ ($currentListStatus ?? null) ? 'active' : '' }}"
                        data-url="{{ route('articles.bookmarks.store', $article) }}">
                    <i class="fa fa-bookmark"></i> <span class="bm-text">{{ ($currentListStatus ?? null) ? __('messages.chapter.bookmarked') : __('messages.chapter.bookmark') }}</span>
                </button>
            @else
                <a href="{{ route('login') }}" class="btn btn-invincible">
                    <i class="fa fa-heart"></i> {{ __('messages.chapter.give_thanks') }} | <span>{{ (int) ($chapterLikesCount ?? 0) }}</span>
                </a>
                <a href="{{ route('login') }}" class="btn btn-invincible bookmark">
                    <i class="fa fa-bookmark"></i> {{ __('messages.chapter.bookmark') }}
                </a>
            @endauth
        </div>
    </div>
    <div class="pagination">
        @if($prevUrl)
            <a href="{{ $prevUrl }}" class="btn"><i class="fa fa-angle-left"></i> {{ __('messages.chapter.previous_chapter') }}</a>
        @endif
        @if($nextUrl)
            <a href="{{ $nextUrl }}" class="btn">{{ __('messages.chapter.next_chapter') }} <i class="fa fa-angle-right"></i></a>
        @endif
    </div>
</div>

<div class="split"></div>

{{-- Comments --}}
<div class="chapter-comments">
    <section class="section comments-section" style="max-width:900px;margin:0 auto;padding:0 16px">
        <h2>{{ __('messages.chapter.comments') }}</h2>
        @auth
            <form method="POST" action="{{ route('articles.comments.store', $article->id) }}" style="margin-bottom:20px">
                @csrf
                <div class="text-input">
                    <textarea name="content" placeholder="{{ __('messages.chapter.write_comment_placeholder') }}" required
                              style="width:100%;min-height:70px;padding:10px;border-radius:4px;border:1px solid var(--border);background:var(--bg-secondary);color:var(--text);resize:vertical"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:8px">{{ __('messages.chapter.send') }}</button>
            </form>
        @else
            <p class="meta-color">{{ __('messages.chapter.please') }} <a href="{{ route('login') }}">{{ __('messages.chapter.login') }}</a> {{ __('messages.chapter.to_comment') }}</p>
        @endauth

        <ul class="comments" style="list-style:none;padding:0">
            @foreach($comments as $comment)
                @php $myVote = $comment->my_vote; @endphp
                <li class="comment" style="margin-bottom:16px">
                    <div class="comment-header">
                        <div class="comment-header__info">
                            <span class="comment-header__username" style="font-weight:600">{{ optional($comment->user)->name ?? __('messages.chapter.anonymous') }}</span>
                            <span class="meta-color" style="font-size:12px;margin-left:8px">{{ optional($comment->created_at)->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="comment-body">
                        <div class="content">{{ $comment->content }}</div>
                    </div>
                    <div class="comment-controls">
                        <div class="left"></div>
                        <div class="right comment-vote" data-id="{{ $comment->id }}">
                            <div class="btn btn-invincible like {{ $myVote === 1 ? 'active' : '' }}" data-vote="1" title="{{ __('messages.comments.like') }}"><i class="fa fa-chevron-up"></i></div>
                            <span class="vote-score">{{ (int) $comment->score }}</span>
                            <div class="btn btn-invincible dislike {{ $myVote === 1 ? '' : 'disabled' }}" data-vote="-1" title="{{ __('messages.comments.dislike') }}"><i class="fa fa-chevron-down"></i></div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        {{ $comments->links() }}
    </section>
</div>

{{-- Table of contents panel --}}
<div class="chapter-panel" id="all-chapters" style="display:none">
    <div class="chapter-settings__header">
        <span>{{ __('messages.chapter.chapter_list') }}</span>
        <button class="btn btn-invincible open-close" p-target="all-chapters"><i class="fa fa-close"></i></button>
    </div>
    <div class="chapter-list">
        @foreach($articleChapters as $ch)
            <a href="{{ route('articles.chapters.show', [$article, $ch->number]) }}"
               class="chapter {{ $ch->number == $chapter->number ? 'active' : '' }}">
                {{ __('messages.chapter.chapter') }} {{ $ch->number }}: {{ $ch->title }}
            </a>
        @endforeach
    </div>
</div>

{{-- Settings panel (overlay bản gốc) --}}
<div id="chapter-settings" class="fullscreen hide">
    <div class="chapter-panel">
        <div class="chapter-settings__header">
            <div class="title">{{ __('messages.chapter.reading_settings') }}</div>
            <div class="btn header-btn open-close" p-target="chapter-settings"><i class="fa fa-close"></i></div>
        </div>
        <div class="chapter-settings__content">
            <div class="name-option">{{ __('messages.chapter.themes') }}</div>
            <div class="themes">
                <button type="button" class="btn-theme" theme="none">A</button>
                <button type="button" class="btn-theme" theme="green-theme">A</button>
                <button type="button" class="btn-theme" theme="light-sephia-theme">A</button>
                <button type="button" class="btn-theme" theme="sephia-theme">A</button>
                <button type="button" class="btn-theme" theme="light-dark-theme">A</button>
                <button type="button" class="btn-theme" theme="dark-theme">A</button>
            </div>
            <div id="font-size" class="field">
                <div class="name-option">{{ __('messages.chapter.font_size') }}</div>
                <div class="control">
                    <button type="button" class="btn btn-invincible btn-lower">-</button>
                    <span>18</span>
                    <button type="button" class="btn btn-invincible btn-upper">+</button>
                </div>
            </div>
            <div id="line-height" class="field">
                <div class="name-option">{{ __('messages.chapter.line_height') }}</div>
                <div class="control">
                    <button type="button" class="btn btn-invincible btn-lower">-</button>
                    <span>1.3</span>
                    <button type="button" class="btn btn-invincible btn-upper">+</button>
                </div>
            </div>
            <div id="margin-bottom" class="field">
                <div class="name-option">{{ __('messages.chapter.paragraph_indent') }}</div>
                <div class="control">
                    <button type="button" class="btn btn-invincible btn-lower">-</button>
                    <span>19</span>
                    <button type="button" class="btn btn-invincible btn-upper">+</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Ad Popup (giữ nguyên logic gốc) --}}
@if($showPopup)
<div id="adPopup" class="ad-popup-overlay">
    <div class="ad-popup-content">
        <div class="ad-popup-header">
            <h4>🎯 {{ __('messages.chapter.support_website') }}</h4>
            <p>{{ __('messages.chapter.ad_popup_message') }}</p>
        </div>
        <div class="ad-popup-body">
            <a href="{{ $affiLink ?? '#' }}" target="_blank" id="adLink" class="ad-link">
                <div class="ad-banner">
                    <img src="{{ $affiImage ?? '' }}" alt="{{ __('messages.chapter.advertisement') }}" style="max-width:100%">
                </div>
            </a>
        </div>
        <div class="ad-packages">
            @php $userPoints = auth()->user()->points ?? 0; @endphp
            @auth<p class="meta-color">{{ __('messages.chapter.you_have') }} <strong>{{ number_format($userPoints) }}</strong> {{ __('messages.chapter.coins') }}</p>@endauth
            <h5>🎁 {{ __('messages.chapter.or_upgrade_premium') }}</h5>
            <form id="vipForm" style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center">
                @foreach(getPremiumPackages() as $i => $pkg)
                <div class="package-option" onclick="$(this).find('input').prop('checked',true).trigger('change')">
                    <input type="radio" name="vip_package" value="{{ $i }}">
                    <div><strong>{{ $pkg['name'] }}</strong> - {{ number_format($pkg['coins']) }} {{ __('messages.chapter.coins') }}<br>
                        <small>{{ $pkg['days'] }} {{ __('messages.chapter.vip_days') }}</small></div>
                </div>
                @endforeach
            </form>
        </div>
        <button type="button" class="btn btn-primary" id="buyVipBtn" style="width:100%;margin-top:10px">{{ __('messages.chapter.buy_now') }}</button>
        <button type="button" class="btn" id="depositBtn" style="display:none;width:100%;margin-top:6px">{{ __('messages.chapter.deposit_more') }}</button>
        <div id="paymentInfo" style="display:none;margin-top:15px;text-align:left">
            <p>{{ __('messages.chapter.contact_admin_to_deposit') }}</p>
        </div>
    </div>
</div>
@endif

@auth
{{-- Report chapter modal --}}
<div id="chapter-report-overlay" class="chapter-report-overlay" hidden>
    <div class="chapter-report-box">
        <h4>{{ __('messages.chapter.report_modal_title') }}</h4>
        <p class="meta-color">{{ __('messages.chapter.report_modal_desc') }}</p>
        <textarea id="chapter-report-reason" rows="4" placeholder="{{ __('messages.chapter.report_placeholder') }}"></textarea>
        <div class="chapter-report-actions">
            <button type="button" class="btn btn-invincible" id="chapter-report-cancel">{{ __('messages.chapter.report_cancel') }}</button>
            <button type="button" class="btn btn-primary" id="chapter-report-submit">{{ __('messages.chapter.report_submit') }}</button>
        </div>
    </div>
</div>
@endauth

<script src="{{ asset('static/core/js/swiper.bundle.js') }}"></script>
<script src="{{ asset('static/core/js/popper.js') }}"></script>
<script src="{{ asset('static/core/js/mainee8b.js') }}?ver=1.8.0"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
window.CSRF_TOKEN = "{{ csrf_token() }}";

// Phím tắt
document.addEventListener('keydown', function(e) {
    @if($showPopup) return; @endif
    if (e.key === 'ArrowLeft' || e.key === 'a') { @if($prevUrl) window.location='{{ $prevUrl }}'; @endif }
    if (e.key === 'ArrowRight' || e.key === 'd') { @if($nextUrl) window.location='{{ $nextUrl }}'; @endif }
});

// ===== Cài đặt đọc: Themes / Font Size / Line Height / Indent (client-side) =====
(function() {
    const reader = document.getElementById('chapter-c');
    if (!reader) return;
    const THEMES = ['none', 'green-theme', 'light-sephia-theme', 'sephia-theme', 'light-dark-theme', 'dark-theme'];
    const DEFAULTS = { fontSize: 18, lineHeight: 1.3, marginBottom: 19, theme: 'none' };
    const num = (k, d) => { const v = parseFloat(localStorage.getItem('reader.' + k)); return isNaN(v) ? d : v; };
    const rs = {
        fontSize: num('fontSize', DEFAULTS.fontSize),
        lineHeight: num('lineHeight', DEFAULTS.lineHeight),
        marginBottom: num('marginBottom', DEFAULTS.marginBottom),
        theme: localStorage.getItem('reader.theme') || DEFAULTS.theme,
    };

    function apply() {
        reader.style.fontSize = rs.fontSize + 'px';
        reader.style.lineHeight = rs.lineHeight;
        reader.style.setProperty('--reader-p-margin', rs.marginBottom + 'px');
        THEMES.forEach(t => { if (t !== 'none') document.body.classList.remove(t); });
        if (rs.theme && rs.theme !== 'none') document.body.classList.add(rs.theme);
    }

    // Themes
    document.querySelectorAll('#chapter-settings .btn-theme').forEach(btn => {
        btn.addEventListener('click', function() {
            rs.theme = this.getAttribute('theme');
            localStorage.setItem('reader.theme', rs.theme);
            apply();
        });
    });

    // Fields (+/-)
    function bindField(id, key, min, max, step, decimals) {
        const field = document.getElementById(id);
        if (!field) return;
        const span = field.querySelector('span');
        const show = () => span.textContent = decimals ? rs[key].toFixed(decimals) : rs[key];
        show();
        const change = (dir) => {
            rs[key] = Math.min(max, Math.max(min, Math.round((rs[key] + dir * step) * 100) / 100));
            show();
            localStorage.setItem('reader.' + key, rs[key]);
            apply();
        };
        field.querySelector('.btn-lower').addEventListener('click', () => change(-1));
        field.querySelector('.btn-upper').addEventListener('click', () => change(1));
    }
    bindField('font-size', 'fontSize', 14, 36, 1, 0);
    bindField('line-height', 'lineHeight', 1, 2.4, 0.1, 1);
    bindField('margin-bottom', 'marginBottom', 5, 45, 1, 0);

    apply();

    // Click nền tối để đóng panel (đi qua nút X để mainee8b mở khoá cuộn)
    document.getElementById('chapter-settings')?.addEventListener('click', function(e) {
        if (e.target === this) this.querySelector('.open-close')?.click();
    });
})();

// Table of contents button
document.getElementById('table-of-contents-btn')?.addEventListener('click', function() {
    const panel = document.getElementById('all-chapters');
    if (panel) panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
});

// Ẩn header khi cuộn xuống (giữ lại hành vi từ chapteree8b.js)
(function() {
    let lastY = 0;
    window.addEventListener('scroll', function() {
        const y = window.scrollY;
        if (y > lastY && y > 60) document.body.classList.add('scrolled');
        else document.body.classList.remove('scrolled');
        lastY = y;
    }, { passive: true });
    document.documentElement.addEventListener('click', function() {
        document.body.classList.remove('scrolled');
    });
})();

// Toast nhỏ tự chứa
function chapterToast(msg, type) {
    const t = document.createElement('div');
    t.className = 'chapter-toast chapter-toast--' + (type || 'note');
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2600);
}

// Vote bình luận (giảm chỉ bấm được khi đã tăng)
(function() {
    const VOTE_BASE = @json(url('comments'));
    const LOGIN_URL = @json(route('login'));
    const IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};
    const CSRF = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('.chapter-comments .comment-vote').forEach(wrap => {
        wrap.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (btn.classList.contains('dislike') && btn.classList.contains('disabled')) return;
                if (!IS_AUTH) { window.location = LOGIN_URL; return; }
                const id = wrap.getAttribute('data-id');
                const val = btn.getAttribute('data-vote');
                wrap.querySelectorAll('.btn').forEach(b => b.style.pointerEvents = 'none');
                fetch(VOTE_BASE + '/' + id + '/vote', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'value=' + encodeURIComponent(val)
                }).then(r => r.json()).then(res => {
                    if (res && res.ok) {
                        wrap.querySelector('.vote-score').textContent = res.score;
                        wrap.querySelector('.like').classList.toggle('active', res.myVote === 1);
                        wrap.querySelector('.dislike').classList.toggle('disabled', res.myVote !== 1);
                    }
                }).finally(() => { wrap.querySelectorAll('.btn').forEach(b => b.style.pointerEvents = ''); });
            });
        });
    });
})();

@auth
// Bookmark đoạn đang đọc + Report chương
(function() {
    const BOOKMARK_URL = @json(route('articles.chapters.bookmarkParagraph', [$article, $chapter->number]));
    const REPORT_URL   = @json(route('articles.chapters.report', [$article, $chapter->number]));
    const CSRF = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content;
    const body = document.body;
    const content = document.getElementById('chapter-c');
    const paragraphs = content ? Array.from(content.querySelectorAll(':scope > p')) : [];

    function postForm(url, data) {
        const fd = new FormData();
        fd.append('_token', CSRF);
        Object.keys(data).forEach(k => fd.append(k, data[k]));
        return fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
            .then(r => r.json());
    }
    function markParagraph(index) {
        paragraphs.forEach((p, i) => p.classList.toggle('bookmark-p', i === index));
    }

    // Khôi phục vị trí đã lưu (server lưu 1-based; 0 = chưa có)
    const saved = parseInt(body.getAttribute('chapter_ph') || '0', 10);
    if (saved > 0 && paragraphs[saved - 1]) {
        markParagraph(saved - 1);
        paragraphs[saved - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Bật/tắt chế độ chọn đoạn
    let selecting = false;
    document.getElementById('bookmark-ph-btn')?.addEventListener('click', function() {
        selecting = !selecting;
        body.classList.toggle('bookmark-ph-process', selecting);
    });

    // Click vào đoạn để lưu vị trí
    paragraphs.forEach((p, i) => {
        p.addEventListener('click', function() {
            if (!selecting) return;
            selecting = false;
            body.classList.remove('bookmark-ph-process');
            postForm(BOOKMARK_URL, { paragraph: i + 1 }).then(res => {
                if (res && res.success) {
                    markParagraph(i);
                    chapterToast(@json(__('messages.chapter.bookmark_saved')), 'success');
                }
            }).catch(() => chapterToast(@json(__('messages.chapter.action_failed')), 'error'));
        });
    });

    // Report chương
    const overlay = document.getElementById('chapter-report-overlay');
    document.getElementById('report-chapter-btn')?.addEventListener('click', () => { if (overlay) overlay.hidden = false; });
    document.getElementById('chapter-report-cancel')?.addEventListener('click', () => { overlay.hidden = true; });
    overlay?.addEventListener('click', (e) => { if (e.target === overlay) overlay.hidden = true; });
    document.getElementById('chapter-report-submit')?.addEventListener('click', function() {
        const reasonEl = document.getElementById('chapter-report-reason');
        this.disabled = true;
        postForm(REPORT_URL, { reason: reasonEl.value.trim() }).then(res => {
            overlay.hidden = true;
            reasonEl.value = '';
            chapterToast((res && res.message) || @json(__('messages.chapter.report_sent')), 'success');
        }).catch(() => chapterToast(@json(__('messages.chapter.action_failed')), 'error'))
          .finally(() => { this.disabled = false; });
    });

    // Give thanks (like chương)
    const likeBtn = document.getElementById('btn-like-chapter');
    likeBtn?.addEventListener('click', function() {
        this.style.pointerEvents = 'none';
        postForm(this.dataset.url, {}).then(res => {
            if (res && res.success) {
                document.getElementById('likes-count').textContent = res.count;
                this.classList.toggle('liked', res.liked);
            }
        }).catch(() => chapterToast(@json(__('messages.chapter.action_failed')), 'error'))
          .finally(() => { this.style.pointerEvents = ''; });
    });

    // Bookmark (thêm/xoá truyện khỏi danh sách)
    const BM_ON = @json(__('messages.chapter.bookmarked'));
    const BM_OFF = @json(__('messages.chapter.bookmark'));
    const bmBtn = document.getElementById('btn-bookmark-chapter');
    bmBtn?.addEventListener('click', function() {
        const active = this.classList.contains('active');
        this.style.pointerEvents = 'none';
        postForm(this.dataset.url, { status: active ? 'remove' : 'reading' }).then(res => {
            if (res && res.success) {
                const nowActive = !!res.status;
                this.classList.toggle('active', nowActive);
                this.querySelector('.bm-text').textContent = nowActive ? BM_ON : BM_OFF;
                chapterToast(nowActive ? BM_ON : BM_OFF, 'success');
            }
        }).catch(() => chapterToast(@json(__('messages.chapter.action_failed')), 'error'))
          .finally(() => { this.style.pointerEvents = ''; });
    });
})();
@endauth

@if($showPopup)
const CHAPTER_SELECT_VIP_PACKAGE_MSG = @json(__('messages.chapter.select_vip_package_alert'));
$(document).ready(function() {
    $('#adLink').on('click', function() {
        $.post('{{ route('articles.chapters.markAdClicked', [$article, $chapter->number]) }}', {_token:'{{ csrf_token() }}'}, function(r) {
            if (r.success) $('#adPopup').hide();
        });
    });
    $('#buyVipBtn').on('click', function() {
        const sel = $('input[name="vip_package"]:checked');
        if (!sel.length) { alert(CHAPTER_SELECT_VIP_PACKAGE_MSG); return; }
        @guest window.location='{{ route('login') }}'; return; @endguest
        fetch('{{ route('vip.buy') }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({package_id: sel.val()})
        }).then(r=>r.json()).then(d=>{
            if(d.success){alert(d.message);$('#adPopup').hide();setTimeout(()=>location.reload(),500);}
            else{$('#paymentInfo').show();}
        });
    });
    $('#depositBtn').on('click', function() {
        @guest window.location='{{ route('login') }}'; @endguest
        $('#paymentInfo').show();
    });
    $('input[name="vip_package"]').on('change', function() {
        $('#buyVipBtn').show(); $('#depositBtn').hide();
    });
});
@endif
</script>

<style>
#chapter-c { max-width:780px;margin:0 auto;padding:20px 16px;font-size:17px;line-height:1.9;color:var(--text-color) }
/* Paywall chương trả phí */
.chapter-paywall { text-align:center; max-width:780px; margin:0 auto 28px; padding:0 16px; }
.chapter-paywall .paywall-note { color:var(--meta-color,#888); margin-bottom:12px; }
.chapter-paywall .btn-primary { display:inline-block; background:#1f1f1f; color:#fff; border:none; padding:10px 24px; border-radius:6px; font-weight:600; cursor:pointer; text-decoration:none; }
.chapter-paywall .btn-primary:hover { opacity:.9; }
.chapter-paywall .btn[disabled] { opacity:.5; cursor:not-allowed; }
.chapter-paywall .paywall-topup { margin-top:10px; font-size:13px; }
.chapter-paywall #buy-msg { margin-top:10px; font-size:14px; }
.chapter-paywall #buy-msg.error { color:#e3342f; }
.chapter-paywall #buy-msg.success { color:#1f9d55; }
#chapter-c p { margin: 0 0 var(--reader-p-margin, 1.25em); }
/* Chế độ chọn đoạn để bookmark */
.bookmark-ph-process #chapter-c p { cursor:pointer; }
.bookmark-ph-process #chapter-c p:hover { background:rgba(255,0,0,.06); }
/* Comment vote widget */
.chapter-comments .comment-controls{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:8px}
.chapter-comments .comment-vote{display:flex;align-items:center;gap:6px}
.chapter-comments .comment-vote .btn{min-width:32px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;background:var(--bg-soft,#eceef3);cursor:pointer;font-size:13px;line-height:1;padding:0;border:1px solid var(--border,#dde1e9);color:#5b6472;transition:.15s}
.chapter-comments .comment-vote .btn:hover{background:var(--bg-soft-hover,#dfe3ec);color:#2b303a}
.chapter-comments .comment-vote .btn.like.active{background:rgba(46,160,67,.15);color:#2ea043}
.chapter-comments .comment-vote .btn.disabled{opacity:.4;cursor:not-allowed;pointer-events:none}
.chapter-comments .comment-vote .vote-score{min-width:18px;text-align:center;font-weight:600;font-size:13px}
/* FA subset thiếu chevron-up/down → vẽ tam giác lên/xuống bằng CSS */
.chapter-comments .comment-vote .like,.chapter-comments .comment-vote .dislike{transform:none}
.chapter-comments .comment-vote .fa-chevron-up,.chapter-comments .comment-vote .fa-chevron-down{font-family:inherit}
.chapter-comments .comment-vote .fa-chevron-up::before,.chapter-comments .comment-vote .fa-chevron-down::before{content:"";display:inline-block;width:0;height:0;border:5px solid transparent}
.chapter-comments .comment-vote .fa-chevron-up::before{border-bottom-color:currentColor;border-top:0}
.chapter-comments .comment-vote .fa-chevron-down::before{border-top-color:currentColor;border-bottom:0}
/* Nút Give thanks / Bookmark cuối chương */
.chapter-info-end .buttons{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
.chapter-info-end .buttons .btn-invincible{display:inline-flex;align-items:center;gap:6px;cursor:pointer}
.chapter-info-end .buttons .btn-invincible.liked i,
.chapter-info-end .buttons .btn-invincible.active i{color:#ff0d0d}
/* Toast */
.chapter-toast { position:fixed; left:50%; bottom:28px; transform:translate(-50%,12px); z-index:10000;
    background:#222; color:#fff; padding:10px 18px; border-radius:8px; font-size:14px; max-width:90vw;
    box-shadow:0 6px 24px rgba(0,0,0,.25); opacity:0; transition:opacity .25s, transform .25s; pointer-events:none; }
.chapter-toast.show { opacity:1; transform:translate(-50%,0); }
.chapter-toast--success { background:#1f9d55; }
.chapter-toast--error { background:#e3342f; }
/* Report modal */
.chapter-report-overlay { position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,.5);
    display:flex; align-items:center; justify-content:center; padding:16px; }
.chapter-report-overlay[hidden] { display:none; }
.chapter-report-box { background:#fff; color:#222; border-radius:12px; padding:22px; width:100%; max-width:440px;
    box-shadow:0 20px 60px rgba(0,0,0,.3); }
.chapter-report-box h4 { margin:0 0 6px; }
.chapter-report-box textarea { width:100%; margin:12px 0; padding:10px 12px; border:1px solid #d9dee7;
    border-radius:8px; resize:vertical; font:inherit; outline:none; }
.chapter-report-actions { display:flex; justify-content:flex-end; gap:10px; }
.chapter-inline-ad { display:flex; justify-content:center; margin:42px auto; line-height:1.15; }
.chapter-inline-ad__inner { width:min(100%, 300px); text-align:left; color:#111; font-family:Roboto, sans-serif; font-size:16px; font-weight:700; }
.chapter-inline-ad__inner a { color:inherit; text-decoration:none; }
.chapter-inline-ad__inner img { display:block; width:100%; height:198px; object-fit:cover; }
.chapter-inline-ad__title { padding:6px 4px 0; }
.ad-popup-overlay { position:fixed;inset:0;background:rgba(255,255,255,.85);backdrop-filter:blur(6px);z-index:9999;display:flex;justify-content:center;align-items:center }
.ad-popup-content { background:#fff;border-radius:8px;padding:24px;width:min(600px,92vw);max-height:90vh;overflow-y:auto;box-shadow:0 4px 20px rgba(0,0,0,.2);text-align:center }
.package-option { border:1px solid #ddd;border-radius:6px;padding:10px 14px;cursor:pointer;min-width:140px }
.package-option:hover { background:#f5f5f5 }
.chapter-info-end .buttons { display:flex; justify-content:center; }
.chapter-info-end .btn-add-to-list {
    display:flex;
    justify-content:space-between;
    align-items:center;
    min-width:170px;
    padding:10px 15px;
}
.chapter-info-end .btn-add-to-list .btn-list {
    display:flex;
    align-items:center;
    justify-content:center;
    width:38px;
    margin:-10px -15px -10px 10px;
    background:#ffefef47;
}
</style>

@auth
{{-- Mua chương (paywall) --}}
<script>
(function () {
    var btn = document.getElementById('btn-buy-chapter');
    if (!btn) return;
    var msg = document.getElementById('buy-msg');
    var CSRF = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content;
    btn.addEventListener('click', function () {
        var orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = @json(__('messages.chapter.buy_processing'));
        if (msg) { msg.className = ''; msg.textContent = ''; }
        fetch(btn.dataset.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d && d.success) {
                if (msg) { msg.className = 'success'; msg.textContent = @json(__('messages.chapter.buy_success')); }
                setTimeout(function () { window.location.href = d.redirect || window.location.href; }, 600);
            } else {
                if (msg) { msg.className = 'error'; msg.textContent = (d && d.error) || @json(__('messages.chapter.action_failed')); }
                btn.disabled = false; btn.textContent = orig;
            }
        }).catch(function () {
            if (msg) { msg.className = 'error'; msg.textContent = @json(__('messages.chapter.action_failed')); }
            btn.disabled = false; btn.textContent = orig;
        });
    });
})();
</script>
@endauth

{{-- Quảng cáo "click bất kỳ đâu" (trang chương có layout riêng nên include trực tiếp) --}}
@include('client.partials.ad-click-anywhere')
</body>
</html>
