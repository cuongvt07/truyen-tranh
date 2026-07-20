@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('content')
<div class="alpha-static-page">
    <section class="alpha-static-hero">
        <div class="container">
            <small>Information center</small>
            <h1>{!! $pageIcon !!} {{ $pageTitle }}</h1>
            <p>Read the latest site information, policies, and reader guidance in one place.</p>
        </div>
    </section>

    <div class="container alpha-static-shell">
        <aside class="alpha-static-nav">
            <a href="{{ route_path('pages.help') }}"><i class="fa fa-question-circle"></i> {{ __('messages.footer.faq') }}</a>
            <a href="{{ route_path('pages.terms') }}"><i class="fa fa-file-contract"></i> {{ __('messages.footer.terms') }}</a>
            <a href="{{ route_path('pages.dmca') }}"><i class="fa fa-shield-alt"></i> {{ __('messages.footer.dmca') }}</a>
            <a href="{{ route_path('pages.rules') }}"><i class="fa fa-gavel"></i> {{ __('messages.footer.rules') }}</a>
            <a href="{{ route_path('pages.feedback') }}"><i class="fa fa-comment-dots"></i> {{ __('messages.footer.feedback') }}</a>
        </aside>

        <article class="alpha-static-content">
            {!! $pageContent !!}
        </article>
    </div>
</div>
@endsection
