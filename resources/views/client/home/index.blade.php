@extends('layout.client')

@section('template_title')
{{ __( 'Trang chủ' ) }}
@endsection

@section('content')
<div class="owl-slider">
    <div id="carousel" class="owl-carousel">
        @foreach($hotArticles->take(5) as $article)
            <div class="item hot-slide">
            <div class="slider-image" 
                style="background-image:url('{{ $article->cover_image ?? 'https://via.placeholder.com/800x400?text='.urlencode($article->title) }}')">
                <a href="{{ route('articles.show', $article->id) }}">
                <span class="sr-only">{{ $article->title }}</span>
                </a>
            </div>

            <div class="slider-content">
                <div class="slider-content-inner">
                <h3 class="hot-title">
                    <a href="{{ route('articles.show', $article->id) }}">{{ $article->title }}</a>
                </h3>

                <div class="hot-genres">
                    Thể loại : {{ $article->genres?->pluck('name')->join(' , ') }}
                </div>

                <p class="hot-desc">
                    {{ Str::limit(strip_tags($article->description), 100) }}
                </p>
                </div>
            </div>
            </div>
        @endforeach
    </div>
</div>

@if(isset($banners['banner_top']))
<div id="top_banner_above_hot_articles">
    <a href="{{ $banners['banner_top_url'] ?? '#' }}" target="_blank">
        <img src="{{ asset('storage/' . $banners['banner_top']) }}"
            alt="Banner trên cùng"
            onerror="this.onerror=null; this.src='fallback-top.png';">
    </a>
</div>
@endif
<div class="container hidden-xs" id="intro-index">
    <div class="title-list">
        <h2><a href="{{ route('home.show_hot_articles') }}">Được đọc nhiều nhất</a></h2>
        <a href="{{ route('home.show_hot_articles') }}"><span class="glyphicon glyphicon-fire"></span></a>
    </div>
    @php $i = 1; @endphp
    @foreach($hotArticles as $article)
    <div class="index-intro">
        <div class="item top-{{ $i }}" itemscope itemtype="https://schema.org/Book">
            <a href="{{ route('articles.show', $article->id) }}" itemprop="url">
                @if ($article->is_completed)
                <span class="full-label"></span>
                @endif
                <img src="{{ $article->cover_image }}" width="129" height="192" alt="#"
                    class="img-responsive item-img" itemprop="image" />
                <div class="title">
                    <h3 itemprop="name">{{ $article->title }}</h3>
                </div>
            </a>
        </div>
    </div>
    @php $i++; @endphp
    @endforeach
</div>

<div class="container visible-xs" id="intro-index-mobile">
    <div class="title-list">
        <h2><a href="{{ route('home.show_hot_articles') }}">Được đọc nhiều nhất</a></h2>
        <a href="{{ route('home.show_hot_articles') }}"><span class="glyphicon glyphicon-fire"></span></a>
    </div>

    <div class="section-stories-hot__list">
        @foreach($hotArticles->take(15) as $article)
        <div class="text-center index-intro-mobile position-relative">
            <a href="{{ route('articles.show', $article->id) }}" class="d-block text-decoration-none position-relative">
                @if ($article->is_completed)
                <span class="full-label"></span>
                @endif
                <div class="image-wrapper position-relative">
                    <img src="{{ $article->cover_image }}" class="img-responsive" alt="{{ $article->title }}" />
                    <div class="overlay-title">
                        {{ $article->title }}
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

<div class="container" id="list-index">
    <div id="novel-history-main" class="list list-truyen list-history col-xs-12 col-sm-12 col-md-8 col-truyen-main">
    </div>
    <div class="list list-truyen list-new col-xs-12 col-sm-12 col-md-8 col-truyen-main">
        <div class="title-list">
            <h2>
                <a href="{{ route('home.show_new_update_articles') }}" title="Latest Release">
                    Mới cập nhật
                </a>
            </h2>
            <a href="{{ route('home.show_new_update_articles') }}" title="Latest Release">
                <span class="glyphicon glyphicon-menu-right"></span>
            </a>
        </div>

        @foreach ($newUpdateArticles as $article)
        <div class="row" itemscope="" itemtype="https://schema.org/Book">
            <div class="col-xs-9 col-sm-6 col-md-5 col-title">
                <span class="glyphicon glyphicon-chevron-right"></span>
                <h3 itemprop="name">
                    <a href="{{ route('articles.show', $article->id) }}" itemprop="url">
                        {{ $article->title }}
                    </a>
                </h3>
                <span class="label-title label-new"></span>
                @if ($article->is_completed)
                <span class="label-title label-full"></span>
                @endif
            </div>
            <div class="hidden-xs col-sm-3 col-md-3 col-cat text-888">
                @foreach ($article->genres as $genre)
                <a itemprop="genre" href="{{ route('genres.show', $genre->id) }}" title="{{ $genre->name }}">{{ $genre->name }}</a>,
                @endforeach
            </div>
            <div class="col-xs-3 col-sm-3 col-md-2 col-chap text-info">
                @if ($article->chapters->isEmpty())
                <span class="chapter-text">
                    Chưa có chương nào
                </span>
                @else
                @php
                $newestChapter = $article->newest_chapter;
                @endphp
                <a title="{{ $newestChapter->title }}"
                    href="{{ route('articles.chapters.show', [$article->id, $newestChapter->number]) }}">
                    <span class="chapter-text">
                        {{ $newestChapter->number_text }}
                    </span>
                </a>
                @endif
            </div>
            <div class="hidden-xs hidden-sm col-md-2 col-time text-888">
                {{ $article->updated_at_text }}
            </div>
        </div>
        @endforeach
    </div>
    <div class="visible-md-block visible-lg-block col-md-4 text-center col-truyen-side">
        @include('client.partials.right-sidebar')
    </div>
</div>

<div class="container" id="truyen-slide">
    <div class="list list-thumbnail col-xs-12">
        <div class="title-list">
            <h2>
                <a href="{{ route('home.show_completed_articles') }}" title="Truyện đã hoàn thành">
                    Đã hoàn thành
                </a>
            </h2>
            <a href="{{ route('home.show_completed_articles') }}" title="Truyện đã hoàn thành">
                <span class="glyphicon glyphicon-menu-right"></span>
            </a>
        </div>
        <div class="row">
            @foreach($completedArticles as $article)
            <div class="col-xs-4 col-sm-3 col-md-2">
                <a href="{{ route('articles.show', $article->id) }}" title="{{ $article->title }}">
                    <img src="{{ $article->cover_image }}" width="164" height="245" alt="#" />
                    <div class="caption">
                        <h3>
                            {{ $article->title }}
                        </h3>
                        <small class="btn-xs label-primary">
                            Full - {{ $article->chapters->count() }} chương
                        </small>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- BANNER TRÁI -->
<div id="left_ads_float">
    <a href="{{ $settings['banner_left_url'] ?? '#' }}" target="_blank">
        <img src="{{ asset('storage/' . ($settings['banner_left'] ?? 'images/default_banner_left.jpg')) }}"
             width="120"
             onerror="this.onerror=null; this.src='fallback.png';" />
    </a>
</div>

<!-- BANNER PHẢI -->
<div id="right_ads_float">
    <a href="{{ $settings['banner_right_url'] ?? '#' }}" target="_blank">
        <img src="{{ asset('storage/' . ($settings['banner_right'] ?? 'images/default_banner_right.jpg')) }}"
             width="120"
             onerror="this.onerror=null; this.src='fallback.png';" />
    </a>
</div>

<!-- BANNER DƯỚI -->
<div id="bottom_ads_float">
    <a href="{{ $settings['banner_bottom_url'] ?? '#' }}" target="_blank">
        <img src="{{ asset('storage/' . ($settings['banner_bottom'] ?? 'images/default_banner_bottom.jpg')) }}"
             height="90"
             onerror="this.onerror=null; this.src='fallback.png';" />
    </a>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css" />
<style>
/* ===== Owl Slider - Full CSS (content overlay centered at bottom, no background) ===== */

.sr-only {
  position: absolute !important;
  width: 1px; height: 1px;
  padding: 0; margin: -1px;
  overflow: hidden; clip: rect(0,0,0,0);
  white-space: nowrap; border: 0;
}

.owl-slider {
  margin-bottom: 30px;
  max-width: 100%;
  overflow: hidden;
}

.owl-carousel .item {
  position: relative;
  display: block;
}

/* Ảnh nền lặp ngang để phủ full chiều rộng */
.slider-image {
  width: 100%;
  height: 300px;
  background-repeat: repeat-x;
  background-size: auto 100%;
  background-position: left center;
  position: relative;
  overflow: hidden;
  border-radius: 14px;
}

/* Khung “hot” phát sáng + viền gradient */
.hot-slide .slider-image {
  box-shadow: 0 8px 24px rgba(255, 71, 0, 0.28);
  animation: hotGlow 2.6s ease-in-out infinite;
}
@keyframes hotGlow {
  0%, 100% { box-shadow: 0 8px 24px rgba(255, 71, 0, 0.22); }
  50%      { box-shadow: 0 10px 28px rgba(255, 120, 0, 0.34); }
}

/* Viền gradient mảnh */
.hot-slide .slider-image::before {
  content: "";
  position: absolute;
  inset: 0;
  padding: 2px;
  border-radius: 14px;
  background: linear-gradient(135deg, #ff4d00, #ffb800, #ff4d00);
  -webkit-mask:
    linear-gradient(#000 0 0) content-box,
    linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
          mask-composite: exclude;
  pointer-events: none;
}

/* Gradient mờ đáy ảnh để tăng độ đọc (không phải nền content) */
.hot-slide .slider-image::after {
  content: "";
  position: absolute;
  left: 0; right: 0; bottom: 0;
  height: 45%;
  background: linear-gradient(to top, rgba(0,0,0,.55), rgba(0,0,0,0));
  pointer-events: none;
}

/* Ribbon HOT */
.hot-ribbon {
  position: absolute;
  top: 12px; left: -36px;
  transform: rotate(-45deg);
  background: linear-gradient(90deg, #ff4d00, #ff9a00);
  color: #fff;
  font-weight: 800;
  letter-spacing: 1px;
  padding: 8px 48px;
  box-shadow: 0 4px 12px rgba(255,77,0,0.45);
  z-index: 3;
}
.hot-ribbon span { font-size: 12px; }

/* Content overlay: center bottom, no background */
.owl-carousel .slider-content {
  position: absolute;
  bottom: 18px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 40px);
  max-width: 960px;
  text-align: left;
  background: none;
  border: 0;
  box-shadow: none;
  z-index: 2;
}

.owl-carousel .slider-content-inner {
  margin: 0 auto;
}

/* Title */
.hot-title {
  margin: 0 0 6px;
  font-size: 24px;
  font-weight: 800;
  line-height: 1.2;
  text-transform: uppercase;
  letter-spacing: .4px;
}
.hot-title a { color: #fff; text-decoration: none; }
.hot-title a:hover { opacity: .9; }

/* Genres (name joined by " / ") */
.hot-genres {
  font-size: 13px;
  color: #fff;
  margin-bottom: 8px;
  font-weight: 600;
  letter-spacing: .2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Description */
.hot-desc {
  margin: 0;
  font-size: 14px;
  line-height: 1.5;
  color: #f8f8f8;
}

/* Owl nav */
.owl-nav button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255,255,255,0.7) !important;
  color: #333 !important;
  border-radius: 50% !important;
  width: 50px !important;
  height: 50px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
  z-index: 4;
}
.owl-nav button:hover { background: rgba(255,255,255,0.9) !important; }
.owl-nav button svg { width: 20px; height: 20px; fill: #333; }
.owl-nav button.owl-prev { left: 20px; }
.owl-nav button.owl-next { right: 20px; }

.owl-nav {
    display: none;
}

/* Dots */
.owl-dots {
    position: absolute;
    text-align: center;
    z-index: 1;
    top: 90%;
    right: 5%;
}
.owl-dots button.owl-dot {
  width: 12px; height: 12px;
  border-radius: 50%;
  margin: 0 5px;
  background: #ccc;
  transition: background .3s ease, transform .2s ease;
}
.owl-dots button.owl-dot.active {
  background: #ff6a00; /* hợp tông HOT */
  transform: scale(1.05);
}

/* Responsive */
@media (max-width: 991px) {
  .slider-image { height: 260px; }
  .hot-title { font-size: 20px; }
  .hot-desc  { font-size: 13px; }
}
@media (max-width: 767px) {
  .slider-image { height: 220px; }
  .owl-carousel .slider-content { bottom: 14px; }
  .hot-title { font-size: 18px; }
  .hot-genres { font-size: 12px; }
}
@media (max-width: 480px) {
  .slider-image { height: 200px; }
  .hot-title { font-size: 17px; }
  .hot-desc  { font-size: 12px; }
}

</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
$(document).ready(function() {
    // Check if jQuery is loaded before initializing the carousel
    if (typeof jQuery != 'undefined' && typeof $.fn.owlCarousel != 'undefined') {
        $("#carousel").owlCarousel({
            autoplay: true,
            loop: true,
            margin: 0,
            items: 1, // Hiển thị 1 item mỗi lần
            slideBy: 1, // Trượt từng item một
            autoplayTimeout: 3000,
            smartSpeed: 800,
            autoplayHoverPause: true,
            nav: true,
            dots: true,
            lazyLoad: true,
            animateIn: 'fadeIn',
            animateOut: 'fadeOut',
            navText: [
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>',
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>'
            ],
            responsive: {
                0: {
                    nav: false
                },
                768: {
                    nav: true
                }
            }
        });
    } else {
        console.error("jQuery or OwlCarousel not loaded properly");
    }
    
    // Ads float script
    var vtlai_remove_fads = false;
    
    window.vtlai_check_adswidth = function() {
        if (vtlai_remove_fads) {
            document.getElementById('left_ads_float').style.display = 'none';
            document.getElementById('right_ads_float').style.display = 'none';
            return;
        } else if (document.cookie.indexOf('vtlai_remove_float_ads') != -1) {
            vtlai_remove_fads = true;
            vtlai_check_adswidth();
            return;
        } else {
            var lwidth = parseInt(document.body.clientWidth);
            if (lwidth < 1110) {
                document.getElementById('left_ads_float').style.display = 'none';
                document.getElementById('right_ads_float').style.display = 'none';
            } else {
                document.getElementById('left_ads_float').style.display = 'block';
                document.getElementById('right_ads_float').style.display = 'block';
            }
            setTimeout('vtlai_check_adswidth()', 10);
        }
    };
    
    // Initialize ads check
    vtlai_check_adswidth();
});
</script>
@endpush