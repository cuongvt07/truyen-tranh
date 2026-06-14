@extends('client.users.profile')
@section('template_title', 'Reading History - ' . $user->username)

@section('user_content')

<h2 class="user-tab-title"><i class="fa fa-history"></i> Reading History</h2>

@if($history->isEmpty())
    <div class="block"><div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
        <i class="fa fa-history" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
        No reading history yet.
    </div></div>
@else
    <div class="block"><div class="user-list-grid">
        @foreach($history as $item)
            @php $article = $item->article; @endphp
            @if(!$article) @continue @endif
            <div class="item">
                <a href="{{ route('articles.show', $article) }}" class="item-link">
                    <div class="poster image image-cover lazy-load-bg">
                        <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                    </div>
                    <div class="title clamp clamp-2">{{ $article->title }}</div>
                </a>
                @if($item->chapter)
                    <a href="{{ route('articles.chapters.show', [$article, $item->chapter_number]) }}"
                       class="continue" style="font-size:12px;color:var(--meta-color)">
                        <i class="fa fa-book-open"></i> Chapter {{ $item->chapter_number }}
                    </a>
                @endif
                <div style="font-size:11px;color:var(--meta-color);margin-top:4px">
                    <i class="fa fa-clock"></i> {{ $item->read_at->diffForHumans() }}
                </div>
            </div>
        @endforeach
    </div></div>
@endif

@endsection
