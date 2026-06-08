@php
    $prevChapter = $article->chapters()->where('number', '<', $chapter->number)->orderByDesc('number')->first();
    $nextChapter = $article->chapters()->where('number', '>', $chapter->number)->orderBy('number')->first();
    $prevUrl = $prevChapter ? route('articles.chapters.show', [$article->id, $prevChapter->number]) : null;
    $nextUrl = $nextChapter ? route('articles.chapters.show', [$article->id, $nextChapter->number]) : null;
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
<body chapter_ph="">

<header class="header-chapter">
    <a href="{{ route('articles.show', $article->id) }}" class="header-title btn header-btn">
        <span class="clamp clamp-1"><i class="fa fa-arrow-left"></i> {{ $article->title }}</span>
    </a>
    <div class="control-btns">
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
    <div class="chapter-text" id="chapter-c">
        {!! nl2br(e($chapter->content)) !!}
    </div>
</div>

<div class="chapter-team__info">
    <div class="chapter-info-end">
        <div class="chapter_team">
            <div class="text">
                <span>{{ __('messages.chapter.posted_by') }}</span>
                <div class="name">{{ optional($article->user)->name ?? config('app.name') }}</div>
            </div>
        </div>
        <div class="buttons">
            @auth
                <form method="POST" action="{{ route('articles.bookmarks.store', $article->id) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-invincible bookmark"><i class="fa fa-bookmark"></i> {{ __('messages.chapter.follow') }}</button>
                </form>
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
            <a href="{{ route('articles.chapters.show', [$article->id, $ch->number]) }}"
               class="chapter {{ $ch->number == $chapter->number ? 'active' : '' }}">
                {{ __('messages.chapter.chapter') }} {{ $ch->number }}: {{ $ch->title }}
            </a>
        @endforeach
    </div>
</div>

{{-- Settings panel --}}
<div class="chapter-panel" id="chapter-settings" style="display:none">
    <div class="chapter-settings__header">
        <span>{{ __('messages.chapter.reading_settings') }}</span>
        <button class="btn btn-invincible open-close" p-target="chapter-settings"><i class="fa fa-close"></i></button>
    </div>
    <div class="chapter-settings__content" style="padding:16px">
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:12px">
            <span>{{ __('messages.chapter.font_size') }}</span>
            <button class="btn btn-invincible" onclick="changeFontSize(-1)">A-</button>
            <button class="btn btn-invincible" onclick="changeFontSize(1)">A+</button>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
            <span>{{ __('messages.chapter.theme') }}</span>
            <button class="btn btn-invincible" onclick="setTheme('light')">{{ __('messages.chapter.theme_light') }}</button>
            <button class="btn btn-invincible" onclick="setTheme('dark')">{{ __('messages.chapter.theme_dark') }}</button>
            <button class="btn btn-invincible" onclick="setTheme('sepia')">Sepia</button>
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

<script src="{{ asset('static/core/js/swiper.bundle.js') }}"></script>
<script src="{{ asset('static/core/js/popper.js') }}"></script>
<script src="{{ asset('static/core/js/mainee8b.js') }}?ver=1.8.0"></script>
<script src="{{ asset('static/book/js/chapteree8b.js') }}?ver=1.8.0"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
window.CSRF_TOKEN = "{{ csrf_token() }}";

// Phím tắt
document.addEventListener('keydown', function(e) {
    @if($showPopup) return; @endif
    if (e.key === 'ArrowLeft' || e.key === 'a') { @if($prevUrl) window.location='{{ $prevUrl }}'; @endif }
    if (e.key === 'ArrowRight' || e.key === 'd') { @if($nextUrl) window.location='{{ $nextUrl }}'; @endif }
});

function changeFontSize(delta) {
    const el = document.getElementById('chapter-c');
    const cur = parseFloat(getComputedStyle(el).fontSize);
    el.style.fontSize = (cur + delta) + 'px';
    localStorage.setItem('chapterFontSize', cur + delta);
}
function setTheme(t) {
    document.body.setAttribute('data-theme', t);
    localStorage.setItem('chapterTheme', t);
}
(function() {
    const fs = localStorage.getItem('chapterFontSize');
    const th = localStorage.getItem('chapterTheme');
    if (fs) document.getElementById('chapter-c').style.fontSize = fs + 'px';
    if (th) document.body.setAttribute('data-theme', th);
})();

// Table of contents button
document.getElementById('table-of-contents-btn')?.addEventListener('click', function() {
    const panel = document.getElementById('all-chapters');
    if (panel) panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
});

@if($showPopup)
const CHAPTER_SELECT_VIP_PACKAGE_MSG = @json(__('messages.chapter.select_vip_package_alert'));
$(document).ready(function() {
    $('#adLink').on('click', function() {
        $.post('{{ route('articles.chapters.markAdClicked', [$article->id, $chapter->number]) }}', {_token:'{{ csrf_token() }}'}, function(r) {
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
[data-theme="dark"]  { background:#1a1a2e; color:#e0e0e0; }
[data-theme="sepia"] { background:#f4ecd8; color:#5b4636; }
#chapter-c { max-width:780px;margin:0 auto;padding:20px 16px;font-size:17px;line-height:1.9 }
.ad-popup-overlay { position:fixed;inset:0;background:rgba(255,255,255,.85);backdrop-filter:blur(6px);z-index:9999;display:flex;justify-content:center;align-items:center }
.ad-popup-content { background:#fff;border-radius:8px;padding:24px;width:min(600px,92vw);max-height:90vh;overflow-y:auto;box-shadow:0 4px 20px rgba(0,0,0,.2);text-align:center }
.package-option { border:1px solid #ddd;border-radius:6px;padding:10px 14px;cursor:pointer;min-width:140px }
.package-option:hover { background:#f5f5f5 }
</style>
</body>
</html>
