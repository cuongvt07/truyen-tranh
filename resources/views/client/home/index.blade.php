@extends('layout.client')

@section('template_title')
{{ __( 'Trang chủ' ) }}
@endsection

@section('content')
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
<script>
    var vtlai_remove_fads = false;

    function vtlai_check_adswidth() {
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
    }
</script>