@extends('client.users.profile')
@section('template_title', __('messages.account.posted_heading'))

@section('user_content')
<h2>{{ __('messages.account.posted_heading') }}</h2>

@if($articles->isEmpty())
    <div class="nothing">{{ __('messages.account.posted_empty') }}</div>
@else
    <div class="user-list-grid">
        @foreach($articles as $article)
            <div class="item">
                <a href="{{ route('articles.show', $article->id) }}" class="item-link">
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
@endif
@endsection
