@extends('layout.novelight')

@section('template_title', 'FAQ')

@section('content')
<div class="alpha-workspace alpha-help-page">
    <div class="container">
        <section class="alpha-workspace-hero">
            <small>Help center</small>
            <h1>{{ $pageTitle }}</h1>
            <p>Find answers and platform guidance.</p>
        </section>

        <article class="alpha-article-content">
            {!! $pageContent !!}
        </article>
    </div>
</div>
@endsection
