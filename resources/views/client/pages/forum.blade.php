@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">{{ $pageTitle }}</h1>
    {!! $pageContent !!}
</div>
@endsection
