<!DOCTYPE html>
<html>

<head
    prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# book: http://ogp.me/ns/book# profile: http://ogp.me/ns/profile#">
    <meta charset="UTF-8"/>
    <title>
        @if (trim($__env->yieldContent('template_title')))
            @yield('template_title') |
        @endif {{ config('app.name', 'Thích truyện') }}
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=yes">
    <meta name="description" content="Đọc truyện online miễn phí, truyện full, truyện hay mới nhất: ngôn tình, tiên hiệp, kiếm hiệp, đam mỹ. Cập nhật nhanh, giao diện thân thiện trên mọi thiết bị.">
    <meta name="keywords" content="đọc truyện, truyện online, truyện full, ngôn tình, tiên hiệp, kiếm hiệp, truyện hay, truyện miễn phí">
    <meta name="robots" content="index, follow">
    <meta name="google-site-verification" content="your-google-verification-code"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="theme-color" content="#f8f9fa">
    <meta property="og:title" content="@yield('og_title', config('app.name', 'Thích truyện'))"/>
    <meta property="og:description" content="@yield('og_description', 'Đọc truyện online miễn phí, truyện full, truyện hay nhất.')"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="@yield('og_url', request()->url())"/>
    <meta property="og:image" content="{{ asset('storage/' . setting('logo_file')) }}"/>
    <meta property="og:site_name" content="{{ config('app.name', 'Thích truyện') }}"/>
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:title" content="@yield('twitter_title', config('app.name', 'Thích truyện'))"/>
    <meta name="twitter:description" content="@yield('twitter_description', 'Đọc truyện online miễn phí, truyện full, truyện hay nhất.')"/>
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/default-share.jpg'))"/>
    <link rel="canonical" href="@yield('canonical_url', request()->url())"/>
    <link rel="icon" type="image/png" href="/favicon.png"/>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"/>
    <link rel="manifest" href="/manifest.json"/>
    <link rel="preload" href="/resource/style.css" as="style">
    <link rel="preload" href="/resource/js/main.js" as="script">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" type="text/css" href="/resource/style.css"/>
    <script src="/resource/js/main.js"></script>
    <link rel="pingback" href=""/>
</head>

@php
    switch (true)
    {
        case is_route('genres.*'):
                $bodyId = "body_cat";
            break;
            case is_route('articles.*'):
                $bodyId = "body_truyen";
            break;
            case is_route('authors.*'):
                $bodyId = "body_author";
            break;
            case is_route('articles.view_chapter'):
                $bodyId = "body_chapter";
            break;
            default:
                $bodyId = "body_home";
                break;
    }
@endphp

<body id="{{ $bodyId }}">
<div id="wrap">
    @include('client.partials.header')
    @yield('content')
    @include('client.partials.footer')
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v19.0"
        nonce="ggm0ulbL"></script>
<script>
    $('.search-holder input[type="search"]').keydown(function (event) {
        event.stopPropagation();
    });

    let idleTime = 0;
    const MAX_IDLE_SECONDS = 10;

    function resetIdleTimer() {
        idleTime = 0;
    }

    function showAffiliatePopup(data) {
        const popup = document.getElementById('affiliate-popup');
        document.getElementById('affiliate-link').href = data.link;
        document.getElementById('affiliate-image').src = data.image;
        document.getElementById('affiliate-link-text').href = data.link;
        document.getElementById('affiliate-link-text').textContent = data.link;
        popup.style.display = 'block';
    }

    document.addEventListener("DOMContentLoaded", function () {
        setInterval(function () {
            idleTime++;
            if (idleTime >= MAX_IDLE_SECONDS * 60) {
                fetch('{{ route('get.affiliate.popup') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'ok') {
                            showAffiliatePopup(data);
                        }
                    });
                idleTime = 0;
            }
        }, 1000);

        const events = ['mousemove', 'mousedown', 'touchstart', 'click', 'keypress', 'scroll'];
        events.forEach(function (evt) {
            window.addEventListener(evt, resetIdleTimer, false);
        });
    });
</script>

<style>
    #affiliate-popup {
        display: none;
        position: fixed;
        top: 20%;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        z-index: 9999;
        text-align: center;
        width: 700px;
        max-width: 90vw;
        border-radius: 10px;
    }

    #affiliate-popup img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }

    #affiliate-popup .close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        cursor: pointer;
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    @media (max-width: 768px) {
        #affiliate-popup {
            top: 10%;
            width: 95vw;
            padding: 15px;
        }

        #affiliate-popup p {
            font-size: 18px;
        }

        #affiliate-popup .close-btn {
            font-size: 28px;
        }
    }

    @media (max-width: 480px) {
        #affiliate-popup {
            top: 20%;
            padding: 10px;
        }

        #affiliate-popup p {
            font-size: 16px;
        }
    }
</style>

<div id="affiliate-popup">
    <span class="close-btn" onclick="document.getElementById('affiliate-popup').style.display='none'">&times;</span>
    <p>💡 Bạn quan tâm có thể xem liên kết sau:</p>
    <a href="#" id="affiliate-link" target="_blank">
        <img id="affiliate-image" src="" alt="Affiliate">
    </a>
    <div>
        <a href="#" id="affiliate-link-text" target="_blank" style="word-break: break-all; font-size: 22px; color:rgb(240, 105, 16);"></a>
    </div>
</div>


@yield('comment-article-scripts')
@yield('article-scripts')
</body>
</html>
