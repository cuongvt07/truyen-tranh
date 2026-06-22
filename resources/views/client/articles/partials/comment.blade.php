@php
    $u         = $comment->user;
    $ava       = optional($u)->avatar ?: asset('static/account/images/no-ava.jpg');
    $isReply   = $isReply ?? (bool) $comment->parent_id;
    $myVote    = $comment->my_vote;                 // 1 | -1 | null
    $canDelete = auth()->check() && $comment->canBeDeletedBy(auth()->user());
    $profile   = $u ? route('users.show.profile', $u->id) : '#';
@endphp
<li class="comment" id="comment-{{ $comment->id }}" data-id="{{ $comment->id }}">
    <div class="comment-header">
        <a href="{{ $profile }}" class="comment-header__ava image image-cover {{ user_is_vip(optional($u)->id) ? 'vip-ring' : '' }}">
            <img class="lazy-image avatar-image" loading="lazy" src="{{ $ava }}" alt="{{ optional($u)->username }}">
            @include('partials.vip-crown', ['userId' => optional($u)->id])
        </a>

        <div class="comment-header__info">
            <a href="{{ $profile }}" class="comment-header__username">{{ optional($u)->name ?? optional($u)->username ?? __('messages.comments.anonymous') }}</a>
            <div class="comment-header__meta meta-color">
                <span class="date">{{ optional($comment->created_at)->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>
    <div class="comment-body">
        <div class="content">{!! nl2br(e($comment->content)) !!}</div>
    </div>
    <div class="comment-controls">
        <div class="left">
            <a href="#" class="comment-append-btn" data-reply="{{ $comment->id }}">{{ __('messages.comments.reply') }}</a>
            @auth
                <span class="additional" data-menu="{{ $comment->id }}" data-can-delete="{{ $canDelete ? 1 : 0 }}">...</span>
            @endauth
        </div>
        <div class="right comment-vote" data-id="{{ $comment->id }}">
            <div class="btn btn-invincible like {{ $myVote === 1 ? 'active' : '' }}" data-vote="1" title="{{ __('messages.comments.like') }}"><i class="fa fa-chevron-up"></i></div>
            <span class="vote-score">{{ (int) $comment->score }}</span>
            <div class="btn btn-invincible dislike {{ $myVote === 1 ? '' : 'disabled' }}" data-vote="-1" title="{{ __('messages.comments.dislike') }}"><i class="fa fa-chevron-down"></i></div>
        </div>
    </div>

    {{-- Reply form is injected here (JS toggle) --}}
    <div class="comment-append-to"></div>

    @unless($isReply)
        @php $replies = $comment->relationLoaded('replies') ? $comment->replies : collect(); @endphp
        @if($replies->count())
            <a href="#" class="show-replies-btn meta-color" data-parent="{{ $comment->id }}">
                <i class="fa fa-comment-dots"></i> {{ __('messages.comments.view_replies', ['count' => $replies->count()]) }}
            </a>
        @endif
        <ul class="comments comments-reply" data-parent="{{ $comment->id }}" @if($replies->count()) hidden @endif>
            @foreach($replies as $reply)
                @include('client.articles.partials.comment', ['comment' => $reply, 'isReply' => true])
            @endforeach
        </ul>
    @endunless
</li>
