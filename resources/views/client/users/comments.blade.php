@extends('client.users.profile')
@section('template_title', __('messages.account.comments_title', ['name' => $user->username]))

@section('user_content')
@if($message = session('success'))
    <div class="alert-success" style="background:#1e3a1e;border:1px solid #2e5e2e;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#9f9">{{ $message }}</div>
@endif


@if($comments->isEmpty())
    <div class="nothing">{{ __('messages.account.comments_empty') }}</div>
@else
    <div class="block comment-blocks" style="grid-template-columns:1fr">
    <h2 class="user-tab-title">{{ __('messages.account.comments_heading') }}</h2>
        @foreach($comments as $comment)
            @php $article = $comment->article; $commentUser = $comment->user; @endphp
            <div class="comment-block">
                <div class="comment-block__header">
                    <div class="left">
                        <div class="comment-header__ava image image-cover lazy-load-bg">
                            <img class="lazy-image" loading="eager" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                        </div>
                        <a href="{{ route_path('articles.show', $article) }}" class="nickname">{{ $article->title }}</a>
                    </div>
                    <div class="right">
                        <div class="date meta-color">{{ optional($comment->created_at)->format('d.m.Y H:i') }}</div>
                    </div>
                </div>
                <div class="text-info">{!! nl2br(e($comment->content)) !!}</div>
                @if(isMyAccount($currentUser ?? null, $commentUser))
                    <form action="{{ route_path('articles.comments.destroy', [$comment->article_id, $comment->id]) }}" method="post" style="margin-top:6px">
                        @csrf @method('delete')
                        <button class="btn btn-invincible" style="font-size:12px;padding:2px 8px"><i class="fa fa-times"></i> {{ __('messages.account.delete') }}</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
    <div style="margin-top:20px">{{ $comments->links() }}</div>
@endif
@endsection
