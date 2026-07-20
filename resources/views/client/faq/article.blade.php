@extends('layout.novelight')

@php
    $locale = app()->getLocale();
    $articleTitle = $article->{"title_$locale"} ?? $article->title_en;
    $categoryTitle = $category->{"title_$locale"} ?? $category->title_en;
    $content = $locale === 'vi' ? ($article->content_vi ?: $article->content_en) : $article->content_en;
@endphp

@section('template_title', $articleTitle . ' - ' . __('messages.nav.faq'))

@section('content')
<div class="alpha-workspace alpha-help-page">
    <div class="container">
        <div class="alpha-community-shell">
            <aside class="alpha-community-sidebar">
                @foreach($allCategories ?? [] as $cat)
                    @php $catTitle = $cat->{"title_$locale"} ?? $cat->title_en; @endphp
                    <div class="alpha-community-card {{ $cat->id === $category->id ? 'active' : '' }}">
                        <a href="{{ route_path('pages.help.topic', $cat->slug) }}" style="color:inherit;text-decoration:none;font-weight:800">{{ $catTitle }}</a>
                        @if(($cat->articles ?? collect())->isNotEmpty())
                            <ul style="margin:8px 0 0 16px;padding:0">
                                @foreach($cat->articles as $art)
                                    @php $artTitle = $art->{"title_$locale"} ?? $art->title_en; @endphp
                                    <li class="{{ $art->id === $article->id ? 'active' : '' }}" style="margin:6px 0">
                                        <a href="{{ route_path('pages.help.article', [$cat->slug, $art->slug]) }}" style="color:inherit;text-decoration:none;font-size:13px">{{ $artTitle }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </aside>

            <main>
                <article class="alpha-article-content">
                    <div class="alpha-book-breadcrumb">
                        <a href="{{ route_path('pages.help', []) }}">{{ __('messages.nav.faq') }}</a>
                        <span>/</span>
                        <a href="{{ route_path('pages.help.topic', $category->slug) }}">{{ $categoryTitle }}</a>
                    </div>
                    <div class="alpha-topic-row__meta" style="margin-top:10px">
                        <span><i class="fa fa-eye"></i> {{ number_format($article->view_count ?? 0) }}</span>
                        <span>{{ $article->created_at ? $article->created_at->format('d M Y - H:i') : '' }}</span>
                    </div>
                    <h1>{{ $articleTitle }}</h1>
                    <div class="text-info">{!! $content !!}</div>
                </article>
            </main>
        </div>
    </div>
</div>
@endsection
