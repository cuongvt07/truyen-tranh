@extends('layout.novelight')

@section('template_title', __('messages.nav.faq') . ' - ' . config('app.name'))

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
<link rel="stylesheet" href="{{ asset('static/faq/css/faqee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title" style="text-align:center">
        {{ __('messages.faq.title') }}
    </h1>

    <div class="faq-theme-blocks">
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
            <a href="{{ route('pages.faq.topic', $category->slug) }}" class="block faq-theme-card">
                <div class="faq-theme-card__icon">
                    <i class="{{ $iconClass }}"></i>
                </div>
                <h2 class="faq-theme-card__title">{{ $title }}</h2>
                @if($description)
                    <p class="faq-theme-card__description">{{ $description }}</p>
                @endif
                <div class="meta-color faq-theme-card__meta">
                    <i class="fa fa-newspaper"></i>
                    {{ __('messages.faq.articles_count', ['count' => $count]) }}
                </div>
            </a>
        @empty
            <div class="block text-center py-5">
                <p class="meta-color">
                    {{ __('messages.faq.empty_categories') }}
                </p>
            </div>
        @endforelse
    </div>
</div>
@endsection
