@extends('layout.novelight')

@php
    $locale = app()->getLocale();
    $categoryTitle = $category->{"title_$locale"} ?? $category->title_en;
@endphp

@section('template_title', $categoryTitle . ' - ' . __('messages.nav.faq'))

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
                @php
                    $catTitle = $cat->{"title_$locale"} ?? $cat->title_en;
                    $isActive = $cat->id === $category->id;
                @endphp
                <div class="faq-sidebar-topic {{ $isActive ? 'active' : '' }}">
                    <a href="{{ route('pages.faq.topic', $cat->slug) }}" class="faq-sidebar-topic__name">
                        {{ $catTitle }}
                        <span class="badge">{{ $cat->articles_count ?? 0 }}</span>
                    </a>
                    @if($isActive)
                        <ul class="faq-sidebar-topic__list">
                            @foreach($articles as $art)
                                @php
                                    $artTitle = $art->{"title_$locale"} ?? $art->title_en;
                                @endphp
                                <li>
                                    <a href="{{ route('pages.faq.article', [$category->slug, $art->slug]) }}">
                                        {{ $artTitle }}
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
                <a href="{{ route('pages.faq') }}">{{ __('messages.nav.faq') }}</a>
                <span>&gt;</span>
                <span>{{ $categoryTitle }}</span>
            </div>
            <h1 class="page-title">{{ $categoryTitle }}</h1>
            @php
                $description = $category->{"description_$locale"} ?? $category->description_en ?? '';
            @endphp
            @if($description)
                <div class="text-info" style="margin-bottom:16px">{{ $description }}</div>
            @endif

            <ul class="faq-article-list">
                @forelse($articles as $article)
                    @php
                        $artTitle = $article->{"title_$locale"} ?? $article->title_en;
                    @endphp
                    <li>
                        <a href="{{ route('pages.faq.article', [$category->slug, $article->slug]) }}">
                            {{ $artTitle }}
                            @if($article->is_pinned)
                                <i class="fa fa-thumbtack text-warning ml-1" title="{{ __('messages.faq.pinned') }}"></i>
                            @endif
                        </a>
                        <span class="meta-color">
                            <i class="fa fa-eye"></i> {{ number_format($article->view_count ?? 0) }}
                        </span>
                    </li>
                @empty
                    <li class="meta-color">
                        {{ __('messages.faq.empty_articles') }}
                    </li>
                @endforelse
            </ul>

            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
