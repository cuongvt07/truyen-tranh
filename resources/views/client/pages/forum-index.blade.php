@extends('layout.novelight')

@section('template_title', $pageTitle)

@section('content')
<div class="alpha-workspace alpha-forum-page">
    <div class="container">
        <section class="alpha-workspace-hero">
            <small>Community</small>
            <h1>{{ $pageTitle }}</h1>
            <p>Follow updates, reader discussions, author posts, and community announcements.</p>
        </section>

        @if(isset($sections) && $sections->isNotEmpty())
            @foreach($sections as $sectionLabel => $cats)
                @if($sectionLabel !== '__none__')
                    <h2 class="alpha-section-title" style="margin-top:24px">{{ $sectionLabel }}</h2>
                @endif
                <div class="alpha-community-grid">
                    @foreach($cats as $category)
                        <a href="{{ route_path('pages.forum.category', $category->slug) }}" class="alpha-community-card">
                            <div class="alpha-community-card__icon"><i class="fa fa-comments"></i></div>
                            <div>
                                <h2>{{ $category->localizedTitle() }}</h2>
                                @if($category->localizedExcerpt())<p>{{ $category->localizedExcerpt() }}</p>@endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endforeach
        @elseif(isset($categories) && $categories->isNotEmpty())
            <div class="alpha-community-grid">
                @foreach($categories as $category)
                    <a href="{{ route_path('pages.forum.category', $category->slug) }}" class="alpha-community-card">
                        <div class="alpha-community-card__icon"><i class="fa fa-comments"></i></div>
                        <div>
                            <h2>{{ $category->localizedTitle() }}</h2>
                            @if($category->localizedExcerpt())<p>{{ $category->localizedExcerpt() }}</p>@endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="alpha-panel alpha-empty-state"><i class="fa fa-comments"></i>No forum categories yet.</div>
        @endif
    </div>
</div>
@endsection
