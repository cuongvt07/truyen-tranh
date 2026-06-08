@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:14px">
        <div class="container"><h1>{!! $pageIcon !!} {{ $pageTitle }}</h1></div>
    </header>

    <div class="static-page block">
        {!! $pageContent !!}
    </div>
</div>

<style>
.static-page { max-width:860px; margin:0 auto; padding:24px; line-height:1.8; }
.static-page h2 { font-size:20px; margin:22px 0 10px; }
.static-page h3 { font-size:16px; margin:18px 0 8px; }
.static-page p { margin-bottom:12px; color:var(--text,#333); }
.static-page ul { margin:0 0 14px 22px; }
.static-page li { margin-bottom:6px; }
.static-page .faq-q { font-weight:600; margin-top:16px; }
.static-page a { color:var(--primary,#e84040); }
</style>
@endsection
