@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('content')
<main class="alpha-help-page">
    <section class="alpha-help-hero">
        <div class="container">
            <small>Help center</small>
            <h1>{{ $pageTitle }}</h1>
            <p>Find quick answers about accounts, books, bonuses, top ups, subscriptions, and reader settings.</p>
        </div>
    </section>

    <div class="container alpha-help-shell">
        @if(trim(strip_tags($pageContent ?? '')) !== '')
            <article class="alpha-help-intro">
                {!! $pageContent !!}
            </article>
        @endif

        <section class="alpha-help-grid">
            @foreach($categories as $category)
                @php
                    $count = $category->children_count
                        ?? $category->children()->active()->where('page_type', 'faq_article')->count();
                @endphp
                <a href="{{ route_path('pages.help.topic', $category->slug) }}" class="alpha-help-card">
                    <span class="alpha-help-card__icon"><i class="fa fa-question-circle"></i></span>
                    <strong>{{ $category->localizedTitle() }}</strong>
                    @if($category->localizedExcerpt())
                        <p>{{ $category->localizedExcerpt() }}</p>
                    @endif
                    <em>{{ __('messages.faq.articles_count', ['count' => $count]) }}</em>
                </a>
            @endforeach
        </section>
    </div>
</main>
@endsection
