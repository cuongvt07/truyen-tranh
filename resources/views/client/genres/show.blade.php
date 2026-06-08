@extends('layout.novelight')

@section('template_title', __('messages.catalog.genre_label') . ': ' . $genre->name)

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/core/css/catalogee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">

    <header class="header-manga" style="margin-bottom:0">
        <div class="container">
            <h1><i class="fa fa-layer-group"></i> {{ $genre->name }}</h1>
            @if($genre->description)
                <p class="meta-color" style="font-size:14px;margin-top:4px">{{ $genre->description }}</p>
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
                <a href="{{ route('articles.show', $article->id) }}" class="manga-item catalog-item">
                    <div class="poster image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="lazy" src="{{ $poster }}" alt="{{ $article->title }}">
                        @if($article->is_completed)
                            <span style="position:absolute;top:4px;right:4px;background:var(--primary);color:#fff;font-size:10px;padding:2px 5px;border-radius:3px">Full</span>
                        @endif
                    </div>
                    <div class="manga-list__info">
                        <div class="title clamp clamp-2">{{ $article->title }}</div>
                        @if($newest)
                            <div class="meta-color" style="font-size:12px">{{ __('messages.catalog.chapter', ['number' => $newest->number]) }}</div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="nothing" style="padding:40px 0;text-align:center">{{ __('messages.catalog.no_results_in_genre') }}</div>
            @endforelse
        </div>
    </section>

    <div style="display:flex;justify-content:center;padding:20px 0">
        {{ $articles->links() }}
    </div>

</div>
@endsection
