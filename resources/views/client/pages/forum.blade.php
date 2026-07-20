@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('content')
<div class="alpha-workspace alpha-forum-page">
    <div class="container">
        <section class="alpha-workspace-hero">
            <small>Community</small>
            <h1>{{ $pageTitle }}</h1>
            <p>Follow updates, discussions, and reader announcements.</p>
        </section>

        <article class="alpha-article-content">
            {!! $pageContent !!}
        </article>
    </div>
</div>
@endsection
