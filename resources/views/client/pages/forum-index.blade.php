@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('page_css')
<link rel="stylesheet" href="{{ asset('static/forum/css/forum.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">{{ $pageTitle }}</h1>

    @if(isset($sections) && $sections->isNotEmpty())
        @foreach($sections as $sectionLabel => $cats)
            @if($sectionLabel !== '__none__')
                <div class="forum-section-name block">{{ $sectionLabel }}</div>
            @endif
            <div class="forum-themes">
                @foreach($cats as $category)
                    @php $latest = ($latestPosts ?? collect())->get($category->id); @endphp
                    <a href="{{ route('pages.forum.category', $category->slug) }}" class="forum-theme forum-post block static-page-card">
                        <div class="icon"><i class="fa fa-comments"></i></div>
                        <div class="forum-theme__info">
                            <div class="info">
                                <h2 class="title">{{ $category->localizedTitle() }}</h2>
                                <div class="description meta-color">{{ $category->localizedExcerpt() }}</div>
                            </div>
                            {{-- No stats on index per design --}}
                        </div>
                    </a>
                @endforeach
            </div>
        @endforeach
    @elseif(isset($categories) && $categories->isNotEmpty())
        <div class="forum-themes">
            @foreach($categories as $category)
                <a href="{{ route('pages.forum.category', $category->slug) }}" class="forum-theme forum-post block static-page-card">
                    <div class="icon"><i class="fa fa-comments"></i></div>
                    <div class="forum-theme__info">
                        <div class="info">
                            <h2 class="title">{{ $category->localizedTitle() }}</h2>
                            <div class="description meta-color">{{ $category->localizedExcerpt() }}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
