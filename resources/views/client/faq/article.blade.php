@extends('layout.novelight')

@section('template_title', ($article->{'title_' . app()->getLocale()} ?? $article->title_en) . ' - ' . __('messages.nav.faq'))

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
                    $locale = app()->getLocale();
                    $catTitle = $cat->{"title_$locale"} ?? $cat->title_en;
                @endphp
                <div class="faq-sidebar-topic {{ $cat->id === $category->id ? 'active' : '' }}">
                    <a href="{{ route('pages.faq.topic', $cat->slug) }}" class="faq-sidebar-topic__name">
                        {{ $catTitle }}
                    </a>
                    <ul class="faq-sidebar-topic__list">
                        @foreach($cat->articles ?? [] as $art)
                            @php
                                $artTitle = $art->{"title_$locale"} ?? $art->title_en;
                            @endphp
                            <li class="{{ isset($article) && $art->id === $article->id ? 'active' : '' }}">
                                <a href="{{ route('pages.faq.article', [$cat->slug, $art->slug]) }}">
                                    {{ $artTitle }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- Main --}}
        <div class="faq-main">
            <article class="block forum-single">
                <div class="breadcumps">
                    <a href="{{ route('pages.faq') }}">{{ __('messages.nav.faq') }}</a>
                    <span>&gt;</span>
                    <a href="{{ route('pages.faq.topic', $category->slug) }}">{{ $category->{"title_$locale"} ?? $category->title_en }}</a>
                    <span>&gt;</span>
                    <span>{{ $article->{"title_$locale"} ?? $article->title_en }}</span>
                </div>
                <div class="forum-single-header">
                    <div class="forum-single-header__author meta-color">
                        {{ $article->created_at ? $article->created_at->format('d M Y - H:i:s') : '' }}
                    </div>
                    <div class="forum-single-header__stats meta-color">
                        <span><i class="fa fa-eye"></i> {{ number_format($article->view_count ?? 0) }}</span>
                    </div>
                </div>
                <h1 class="page-title">{{ $article->{"title_$locale"} ?? $article->title_en }}</h1>
                @php
                    $content = $locale === 'vi' ? ($article->content_vi ?: $article->content_en) : $article->content_en;
                @endphp
                <div class="text-info">{!! $content !!}</div>
            </article>
        </div>
    </div>
</div>
@endsection
