@extends('client.users.profile')
@section('template_title', __('messages.account.posted_heading'))

@section('user_content')
<h2 class="user-tab-title">{{ __('messages.account.posted_heading') }}</h2>

@if($articles->isEmpty())
    <div class="block"><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
        <i class="fa fa-book" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
        {{ __('messages.account.posted_empty') }}
    </div></div>
@else
    <div class="block"><div class="user-list-grid">
        @foreach($articles as $article)
            <div class="item">
                <a href="{{ route_path('articles.show', $article) }}" class="item-link">
                    <div class="poster image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                    </div>
                    <div class="title clamp clamp-2">{{ $article->title }}</div>
                </a>
                <div style="font-size:12px;color:var(--meta-color)">
                    <i class="fa fa-calendar"></i> {{ optional($article->updated_at)->format('d.m.Y') }}
                </div>
            </div>
        @endforeach
    </div>
    <div style="margin-top:20px">{{ $articles->links() }}</div>
    </div>
@endif
@endsection
