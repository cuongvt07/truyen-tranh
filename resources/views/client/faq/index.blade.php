@extends('layout.novelight')

@section('template_title', __('messages.nav.faq') . ' - ' . config('app.name'))

@section('content')
<main class="alpha-help-page">
    <section class="alpha-help-hero">
        <div class="container">
            <small>Help center</small>
            <h1>{{ __('messages.faq.title') }}</h1>
            <p>Find answers about accounts, reading, posting, payments, and community features.</p>
        </div>
    </section>

    <div class="container alpha-help-shell">
        <section class="alpha-help-grid">
            @forelse($categories as $category)
                @php
                    $locale = app()->getLocale();
                    $title = $category->{"title_$locale"} ?? $category->title_en;
                    $description = $category->{"description_$locale"} ?? $category->description_en ?? '';
                    $count = $category->articles_count ?? 0;
                    $iconClass = trim($category->icon ?: 'fa-question-circle');
                    $iconClass = $iconClass === 'fa-circle-question' ? 'fa-question-circle' : $iconClass;
                    if (!preg_match('/(^|\s)(fa|fas|far|fab|fa-solid|fa-regular|fa-brands)(\s|$)/', $iconClass)) {
                        $iconClass = 'fa ' . $iconClass;
                    }
                @endphp
                <a href="{{ route_path('pages.help.topic', $category->slug) }}" class="alpha-help-card">
                    <span class="alpha-help-card__icon"><i class="{{ $iconClass }}"></i></span>
                    <strong>{{ $title }}</strong>
                    @if($description)<p>{{ $description }}</p>@endif
                    <em>{{ __('messages.faq.articles_count', ['count' => $count]) }}</em>
                </a>
            @empty
                <div class="alpha-panel alpha-empty-state">
                    <i class="fa fa-question-circle"></i>
                    {{ __('messages.faq.empty_categories') }}
                </div>
            @endforelse
        </section>
    </div>
</main>
@endsection
