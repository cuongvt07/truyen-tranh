@extends('layout.novelight')

@section('template_title', $title)
@section('meta_description', $description ?? $title)

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/core/css/catalogee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">

    <header class="header-manga" style="margin-bottom:0">
        <div class="container">
            <h1>{{ $title }}</h1>
            @if(!empty($description))
                <p class="meta-color" style="font-size:14px;margin-top:4px">{{ $description }}</p>
            @endif
        </div>
    </header>

    <section class="section">
        <div class="manga-list block catalog-list">
            @forelse($articles as $article)
                @php
                    $poster = novel_poster($article);
                    $newest = $article->chapters->isNotEmpty() ? $article->chapters->sortByDesc('number')->first() : null;
                @endphp
                <a href="{{ route('articles.show', $article) }}" class="manga-item catalog-item">
                    <div class="poster image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="lazy" src="{{ $poster }}" alt="{{ $article->title }}">
                        @if($article->is_completed)
                            <span class="label-completed" title="{{ __('messages.catalog.status_completed') }}" style="position:absolute;top:4px;right:4px;background:#2e9c5a;color:#fff;font-size:11px;font-weight:700;line-height:1;width:18px;height:18px;display:flex;align-items:center;justify-content:center;border-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,.35)">C</span>
                        @endif
                    </div>
                    <div class="manga-list__info">
                        <div class="title clamp clamp-2">{{ $article->title }}</div>
                        @if($article->authors->count())
                            <div class="meta-color" style="font-size:12px"><i class="fa fa-user"></i> {{ $article->authors->first()->name }}</div>
                        @endif
                        @if($newest)
                            <div class="meta-color" style="font-size:12px"><i class="fa fa-book"></i> {{ __('messages.catalog.chapter', ['number' => $newest->number]) }}</div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="nothing" style="padding:40px 0;text-align:center">{{ __('messages.catalog.no_results') }}</div>
            @endforelse
        </div>
    </section>

    <div style="display:flex;justify-content:center;padding:20px 0">
        {{ $articles->links() }}
    </div>

</div>
@endsection
