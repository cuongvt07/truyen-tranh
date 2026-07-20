@extends('layout.novelight')

@section('template_title', $article->localizedTitle() . ' - FAQ')

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
                    <ul class="faq-sidebar-topic__list">
                        @foreach($cat->children ?? [] as $art)
                            <li class="{{ isset($article) && $art->id === $article->id ? 'active' : '' }}">
                                <a href="{{ route_path('pages.faq.article', [$cat->slug, $art->slug]) }}">
                                    {{ $art->localizedTitle() }}
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
                    <a href="{{ route_path('pages.faq') }}">FAQ</a>
                    <span>&gt;</span>
                    <a href="{{ route_path('pages.faq.topic', $category->slug) }}">{{ $category->localizedTitle() }}</a>
                    <span>&gt;</span>
                    <span>{{ $article->localizedTitle() }}</span>
                </div>
                <div class="forum-single-header">
                    <div class="forum-single-header__author meta-color">
                        {{ $article->created_at ? $article->created_at->format('d M Y - H:i:s') : '' }}
                    </div>
                    <div class="forum-single-header__stats meta-color">
                        <span><i class="fa fa-eye"></i> {{ number_format($article->view_count ?? 0) }}</span>
                        <span><i class="fa fa-comment"></i> {{ $article->comments()->count() }}</span>
                    </div>
                </div>
                <h1 class="page-title">{{ $article->localizedTitle() }}</h1>
                <div class="text-info">{!! $article->localizedContent() !!}</div>
            </article>

            @include('client.pages._static-page-comments', ['commentPage' => $article, 'comments' => $comments])
        </div>
    </div>
</div>
@endsection
