@extends('layout.novelight')

@section('template_title', $character->name)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($character->description ?? ''), 160) ?: $character->name)

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/core/css/catalogee8b.css') }}?ver=1.8.0">
@endsection

@section('content')
@php
    $typeLabel = [
        __('messages.community.character_main'),
        __('messages.community.character_supporting'),
        __('messages.community.character_other'),
    ][$character->type] ?? __('messages.community.character_other');
@endphp
<div class="container">

    {{-- Thông tin nhân vật --}}
    <div class="block character-block">
        <div class="character-info">
            <div class="character-photo">
                <img src="{{ $character->photo ?: asset('static/account/images/no-ava.jpg') }}" alt="{{ $character->name }}">
            </div>
            <div class="character-meta">
                <h1>{{ $character->name }}</h1>
                <div class="character-tag">{{ $typeLabel }}</div>
                @if(filled($character->description))
                    <div class="character-desc meta-color">{!! nl2br(e($character->description)) !!}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Danh sách truyện nhân vật xuất hiện --}}
    <section class="section">
        <header class="header-manga" style="margin-bottom:14px">
            <div class="container">
                <h2 style="font-size:18px;margin:0">
                    <i class="fa fa-book"></i>
                    {{ __('messages.community.appears_in_count', ['count' => $articles->total()]) }}
                </h2>
            </div>
        </header>

        <div class="manga-grid-list">
            @forelse($articles as $article)
                <a href="{{ route('articles.show', $article) }}" class="item">
                    <div class="poster image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="lazy" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                        @if($article->is_completed)<span class="grid-badge" title="{{ __('messages.ui.full') }}">C</span>@endif
                    </div>
                    <div class="title clamp clamp-2">{{ $article->title }}</div>
                </a>
            @empty
                <div class="nothing" style="grid-column:1/-1;text-align:center;padding:40px 0;color:var(--meta-color)">
                    {{ __('messages.community.no_appearances') }}
                </div>
            @endforelse
        </div>

        <div style="margin-top:16px">{{ $articles->links('vendor.pagination.novelight') }}</div>
    </section>

</div>

<style>
.character-block { padding:20px; margin-bottom:18px; }
.character-info { display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap; }
.character-photo { width:120px; height:120px; flex-shrink:0; border-radius:50%; overflow:hidden; }
.character-photo img { width:100%; height:100%; object-fit:cover; }
.character-meta { flex:1; min-width:0; }
.character-meta h1 { margin:0 0 6px; font-size:24px; }
.character-tag { display:inline-block; background:var(--primary,#e84040); color:#fff; font-size:12px; padding:3px 10px; border-radius:12px; margin-bottom:10px; }
.character-desc { font-size:14px; line-height:1.6; }
.manga-grid-list { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; }
.manga-grid-list .item { display:block; }
.manga-grid-list .item .poster { position:relative; aspect-ratio:2/3; border-radius:6px; overflow:hidden; }
.manga-grid-list .item .poster img { width:100%; height:100%; object-fit:cover; }
.manga-grid-list .item .grid-badge { position:absolute; top:4px; right:4px; background:#2e9c5a; color:#fff; font-size:11px; font-weight:700; line-height:1; width:18px; height:18px; display:flex; align-items:center; justify-content:center; border-radius:4px; box-shadow:0 1px 3px rgba(0,0,0,.35); }
.manga-grid-list .item .title { margin-top:6px; font-size:13px; line-height:1.35; }
@media (max-width:1024px){ .manga-grid-list { grid-template-columns:repeat(4,1fr); } }
@media (max-width:640px){ .manga-grid-list { grid-template-columns:repeat(3,1fr); } .character-photo{ width:90px;height:90px; } }
</style>
@endsection
