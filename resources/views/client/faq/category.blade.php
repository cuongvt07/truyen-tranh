@extends('layout.novelight')

@php
    $locale = app()->getLocale();
    $categoryTitle = $category->{"title_$locale"} ?? $category->title_en;
    $description = $category->{"description_$locale"} ?? $category->description_en ?? '';
@endphp

@section('template_title', $categoryTitle . ' - ' . __('messages.nav.faq'))

@section('content')
<div class="alpha-workspace alpha-help-page">
    <div class="container">
        <section class="alpha-workspace-hero alpha-workspace-hero--plain">
            <small>FAQ topic</small>
            <h1>{{ $categoryTitle }}</h1>
            @if($description)<p>{{ $description }}</p>@endif
        </section>

        <div class="alpha-community-shell">
            <aside class="alpha-community-sidebar">
                @foreach($allCategories ?? [] as $cat)
                    @php
                        $catTitle = $cat->{"title_$locale"} ?? $cat->title_en;
                        $isActive = $cat->id === $category->id;
                    @endphp
                    <a href="{{ route_path('pages.help.topic', $cat->slug) }}" class="alpha-community-card {{ $isActive ? 'active' : '' }}">
                        <h3>{{ $catTitle }}</h3>
                        <small>{{ __('messages.faq.articles_count', ['count' => $cat->articles_count ?? 0]) }}</small>
                    </a>
                @endforeach
            </aside>

            <main class="alpha-topic-list">
                @forelse($articles as $article)
                    @php $artTitle = $article->{"title_$locale"} ?? $article->title_en; @endphp
                    <article class="alpha-topic-row">
                        <div>
                            <a href="{{ route_path('pages.help.article', [$category->slug, $article->slug]) }}">
                                {{ $artTitle }}
                                @if($article->is_pinned)<i class="fa fa-thumbtack" style="color:#d89000"></i>@endif
                            </a>
                        </div>
                        <div class="alpha-topic-row__meta">
                            <span><i class="fa fa-eye"></i> {{ number_format($article->view_count ?? 0) }}</span>
                        </div>
                    </article>
                @empty
                    <div class="alpha-panel alpha-empty-state">
                        <i class="fa fa-newspaper"></i>
                        {{ __('messages.faq.empty_articles') }}
                    </div>
                @endforelse

                {{ $articles->links() }}
            </main>
        </div>
    </div>
</div>
@endsection
