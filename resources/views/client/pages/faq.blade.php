@extends('layout.novelight')

@section('template_title', 'FAQ')

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/faq/css/faqee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title" style="text-align: center;">{{ $pageTitle }}</h1>
    {!! $pageContent !!}
</div>
@endsection
