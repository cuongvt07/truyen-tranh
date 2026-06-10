@extends('layout.novelight')

@section('template_title', $pageTitle . ' - FAQ')

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/faq/css/faqee8b.css') }}?ver=1.8.0">
@endsection

@php
    $locale = app()->getLocale();
    $topics = [
        'account' => [
            'title' => setting("page_faq_account_title_{$locale}", $locale === 'vi' ? 'Tài khoản' : 'Account'),
            'url'   => route('pages.faq.topic', 'account'),
            'items' => [
                $locale === 'vi' ? 'Làm sao biết mật khẩu sau khi đăng ký bằng Google?' : 'How can I find out my login password (after registering via Google)',
                $locale === 'vi' ? 'Tôi không thể bình luận, đánh giá hoặc đăng truyện sau khi đăng ký' : 'I cannot post comments, reviews, or books after registering',
            ],
        ],
        'general' => [
            'title' => setting("page_faq_general_title_{$locale}", $locale === 'vi' ? 'Chung' : 'General'),
            'url'   => route('pages.faq.topic', 'general'),
            'items' => [
                $locale === 'vi' ? 'Làm sao liên hệ moderator hoặc quản trị viên?' : 'How can I contact a moderator or an administrator?',
                $locale === 'vi' ? 'Vì sao một số truyện bị tắt bình luận?' : 'Why are comments disabled on some titles?',
            ],
        ],
    ];
@endphp

@section('content')
<div class="container">
    <div class="faq-topic-page">
        {{-- Sidebar --}}
        <div class="block faq-sidebar">
            @foreach($topics as $key => $topic)
                <div class="faq-sidebar-topic {{ $activeTopic === $key ? 'active' : '' }}">
                    <a href="{{ $topic['url'] }}" class="faq-sidebar-topic__name">{{ $topic['title'] }}</a>
                    <ul class="faq-sidebar-topic__list">
                        @foreach($topic['items'] as $item)
                            <li><a href="{{ $topic['url'] }}#{{ \Illuminate\Support\Str::slug($item) }}">{{ $item }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- Main content --}}
        <div class="block faq-main">
            <div class="breadcumps">
                <a href="{{ route('pages.faq') }}">FAQ</a>
                <span>&gt;</span>
                <span>{{ $pageTitle }}</span>
            </div>
            <h1 class="page-title">{{ $pageTitle }}</h1>
            <div class="text-info">
                {!! $pageContent !!}
            </div>
        </div>
    </div>
</div>
@endsection
