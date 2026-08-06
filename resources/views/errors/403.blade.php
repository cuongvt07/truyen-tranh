@extends('layout.novelight')

@section('template_title', __('Forbidden'))
@section('robots', 'noindex, nofollow')

@section('content')
<main class="alpha-static-page">
    <section class="alpha-static-hero">
        <div class="container">
            <small>{{ __('messages.access.access_required') }}</small>
            <h1>{{ __('Forbidden') }}</h1>
            <p>{{ $exception->getMessage() ?: __('messages.community.purchase_required') }}</p>
        </div>
    </section>

    <div class="container alpha-static-shell alpha-workspace-hero--plain">
        <article class="alpha-static-content">
            <h2>{{ __('messages.access.continue_reading') }}</h2>
            <p>{{ __('messages.access.access_conditions') }}</p>
            <div class="alpha-form-actions">
                <a href="{{ route_path('pages.gifts', []) }}" class="alpha-btn alpha-btn--primary">
                    <i class="fa fa-coins"></i>
                    <span>{{ __('messages.access.view_coin_packs') }}</span>
                </a>
                <a href="{{ route_path('catalog.index', []) }}" class="alpha-btn">
                    <i class="fa fa-book-open"></i>
                    <span>{{ __('messages.access.browse_novels') }}</span>
                </a>
            </div>
        </article>
    </div>
</main>
@endsection
