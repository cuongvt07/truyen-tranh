@extends('layout.novelight')

@section('template_title', $category->localizedTitle() . ' - FAQ')

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
<link rel="stylesheet" href="{{ asset('static/faq/css/faqee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <div class="faq-topic-page">
        {{-- Sidebar --}}
        <div class="block faq-sidebar">
            @foreach($allCategories ?? [] as $cat)
                <div class="faq-sidebar-topic {{ $cat->id === $category->id ? 'active' : '' }}">
                    <a href="{{ route_path('pages.faq.topic', $cat->slug) }}" class="faq-sidebar-topic__name">
                        {{ $cat->localizedTitle() }}
                    </a>
                    @if($cat->id === $category->id)
                        <ul class="faq-sidebar-topic__list">
                            @foreach($articles as $art)
                                <li>
                                    <a href="{{ route_path('pages.faq.article', [$cat->slug, $art->slug]) }}">
                                        {{ $art->localizedTitle() }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <ul class="faq-sidebar-topic__list">
                            @foreach($cat->children ?? [] as $art)
                                <li>
                                    <a href="{{ route_path('pages.faq.article', [$cat->slug, $art->slug]) }}">
                                        {{ $art->localizedTitle() }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Main content --}}
        <div class="block faq-main">
            <div class="breadcumps">
                <a href="{{ route_path('pages.faq') }}">FAQ</a>
                <span>&gt;</span>
                <span>{{ $category->localizedTitle() }}</span>
            </div>
            <h1 class="page-title">{{ $category->localizedTitle() }}</h1>
            @if($category->localizedExcerpt())
                <div class="text-info" style="margin-bottom:16px">{{ $category->localizedExcerpt() }}</div>
            @endif

            <ul class="faq-article-list">
                @forelse($articles as $article)
                    <li>
                        <a href="{{ route_path('pages.faq.article', [$category->slug, $article->slug]) }}">
                            {{ $article->localizedTitle() }}
                        </a>
                    </li>
                @empty
                    <li class="meta-color">{{ app()->getLocale() === 'vi' ? 'Chưa có bài viết.' : 'No articles yet.' }}</li>
                @endforelse
            </ul>

            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
