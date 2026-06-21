@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
<link rel="stylesheet" href="{{ asset('static/faq/css/faqee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title" style="text-align:center">{{ $pageTitle }}</h1>

    @if(trim(strip_tags($pageContent ?? '')) !== '')
        <div class="block text-info">{!! $pageContent !!}</div>
    @endif

    <div class="faq-theme-blocks">
        @foreach($categories as $category)
            @php
                $count = $category->children_count
                    ?? $category->children()->active()->where('page_type','faq_article')->count();
            @endphp
            <a href="{{ route('pages.faq.topic', $category->slug) }}" class="block faq-theme-card">
                <h2 class="faq-theme-card__title">{{ $category->localizedTitle() }}</h2>
                <div class="meta-color faq-theme-card__meta">
                    <i class="fa fa-newspaper"></i>
                    {{ __('messages.faq.articles_count', ['count' => $count]) }}
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
