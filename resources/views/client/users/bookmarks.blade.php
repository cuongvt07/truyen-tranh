@extends('client.users.profile')
@section('template_title', __('messages.account.bookmarks_title'))

@section('user_content')
@php $isMyAccount = isMyAccount($currentUser ?? null, $user); @endphp

@if($message = session('bookmark_success'))
    <div class="alert-success" style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ $message }}</div>
@endif

<h2 class="user-tab-title">{{ __('messages.account.bookmarks_heading') }}</h2>

@if($bookmarks->isEmpty())
    <div class="block"><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
        <i class="fa fa-bookmark" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
        {{ __('messages.account.bookmarks_empty') }}
    </div></div>
@else
    <div class="block"><div class="user-list-grid">
        @foreach($bookmarks as $bookmark)
            @if(!$bookmark->is_public && !$isMyAccount) @continue @endif
            @php $article = $bookmark->article; @endphp
            <div class="item">
                <a href="{{ route_path('articles.show', $article) }}" class="item-link">
                    <div class="poster image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                    </div>
                    <div class="title clamp clamp-2">{{ $article->title }}</div>
                </a>
                @php $newest = $article->newest_chapter ?? null; @endphp
                @if($newest)
                    <a href="{{ route_path('articles.chapters.show', [$article, $newest->number]) }}" class="continue" style="font-size:12px;color:var(--meta-color)">
                        <i class="fa fa-book"></i> {{ __('messages.account.chapter_number', ['number' => $newest->number]) }}
                    </a>
                @endif
                @if($isMyAccount)
                    <form action="{{ route_path('articles.bookmarks.destroy', [$article->id, $bookmark->id]) }}" method="post" style="margin-top:4px">
                        @csrf @method('delete')
                        <button class="btn btn-invincible" style="font-size:12px;padding:2px 8px"><i class="fa fa-times"></i> {{ __('messages.account.unfollow') }}</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
    <div style="margin-top:20px">{{ $bookmarks->links() }}</div>
    </div>
@endif
@endsection
