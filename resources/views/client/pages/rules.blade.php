@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="block text-info rules-page">
        <h1 class="page-title">{{ $pageTitle }}</h1>
        {!! $pageContent !!}
    </div>

    @if(isset($page, $comments))
        @include('client.pages._static-page-comments', ['commentPage' => $page, 'comments' => $comments])
    @endif
</div>
@endsection

@push('styles')
<style>
    .rules-page {
        line-height: 1.75;
    }
    .rules-page h2 {
        font-size: 22px;
        margin: 18px 0 10px;
    }
    .rules-page h3 {
        font-size: 17px;
        margin: 14px 0 8px;
    }
    .rules-page ul {
        margin: 0 0 14px 22px;
    }
    .rules-page li,
    .rules-page p {
        margin-bottom: 8px;
    }
</style>
@endpush
