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
    <style>
        .lock-wall {
            max-width: 480px;
            margin: 60px auto;
            text-align: center;
            padding: 32px 24px;
            background: var(--bg-secondary, #1e1e2e);
            border-radius: 12px;
            border: 1px solid var(--border, #333);
        }
        .lock-wall .lock-icon { font-size: 48px; margin-bottom: 16px; }
        .lock-wall h2 { margin-bottom: 8px; }
        .lock-wall .credit-cost {
            font-size: 28px;
            font-weight: 700;
            color: #f0c040;
            margin: 12px 0;
        }
        .lock-wall .user-balance {
            font-size: 14px;
            color: var(--text-muted, #888);
            margin-bottom: 20px;
        }
        .lock-wall .btn-unlock {
            display: inline-block;
            padding: 12px 32px;
            background: #f0c040;
            color: #1a1a2e;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .2s;
        }
        .lock-wall .btn-unlock:hover { opacity: .85; }
        .lock-wall .btn-unlock:disabled { opacity: .45; cursor: not-allowed; }
        .lock-wall .btn-login {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 28px;
            background: var(--primary, #7c6af7);
            color: #fff;
            border-radius: 8px;
            font-size: 15px;
            text-decoration: none;
        }
        .lock-wall .topup-link {
            display: block;
            margin-top: 10px;
            font-size: 13px;
            color: var(--text-muted, #888);
        }
        .lock-wall .topup-link a { color: #f0c040; }
        #unlock-msg { margin-top: 14px; font-size: 14px; }
        #unlock-msg.error { color: #ff6b6b; }
        #unlock-msg.success { color: #51cf66; }
    </style>
</head>
<body chapter_ph="">

<header class="header-chapter">
    <a href="{{ route('articles.show', $article->id) }}" class="header-title btn header-btn">
        <span class="clamp clamp-1"><i class="fa fa-arrow-left"></i> {{ $article->title }}</span>
    </a>
</header>

<div class="chapter-control">
    @if($prevUrl)
        <a href="{{ $prevUrl }}" class="btn header-btn"><i class="fa fa-angle-left"></i></a>
    @else
        <span class="btn header-btn disabled"><i class="fa fa-angle-left"></i></span>
    @endif

    <div class="btn header-btn">
        <span>{{ __('messages.chapter.chapter') }} {{ $chapter->number }}</span>
    </div>

    @if($nextUrl)
        <a href="{{ $nextUrl }}" class="btn header-btn"><i class="fa fa-angle-right"></i></a>
    @else
        <span class="btn header-btn disabled"><i class="fa fa-angle-right"></i></span>
    @endif
</div>

<div class="chapter-text__place">
    <div class="lock-wall">
        <div class="lock-icon">🔒</div>
        <h2>{{ __('messages.chapter.chapter') }} {{ $chapter->number }}: {{ $chapter->title }}</h2>
        <p style="color:var(--text-muted,#888);margin-bottom:4px">Chương này yêu cầu credit để mở khoá</p>
        <div class="credit-cost">{{ $creditCost }} credit</div>

        @auth
            <div class="user-balance">Số dư của bạn: <strong>{{ $userPoints }} credit</strong></div>

            @if($userPoints >= $creditCost)
                <button class="btn-unlock" id="btn-unlock"
                        data-url="{{ route('articles.chapters.unlock', [$article->id, $chapter->number]) }}">
                    Mở khoá chương này
                </button>
            @else
                <button class="btn-unlock" disabled>Không đủ credit</button>
                <div class="topup-link">
                    <a href="{{ route('client.paypoints') }}">Nạp thêm credit ngay →</a>
                </div>
            @endif
            <div id="unlock-msg"></div>
        @else
            <p style="color:var(--text-muted,#888);margin-bottom:16px">Đăng nhập để mua và đọc chương này</p>
            <a href="{{ route('login') }}" class="btn-login">Đăng nhập</a>
        @endauth
    </div>
</div>

@auth
<script>
(function () {
    const btn = document.getElementById('btn-unlock');
    if (!btn) return;
    const msg = document.getElementById('unlock-msg');

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.textContent = 'Đang xử lý…';
        msg.className = '';
        msg.textContent = '';

        fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                msg.className = 'success';
                msg.textContent = 'Mở khoá thành công! Đang chuyển hướng…';
                setTimeout(function () { window.location.href = data.redirect; }, 800);
            } else {
                msg.className = 'error';
                msg.textContent = data.error || 'Có lỗi xảy ra.';
                btn.disabled = false;
                btn.textContent = 'Mở khoá chương này';
            }
        })
        .catch(function () {
            msg.className = 'error';
            msg.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
            btn.disabled = false;
            btn.textContent = 'Mở khoá chương này';
        });
    });
})();
</script>
@endauth

</body>
</html>
