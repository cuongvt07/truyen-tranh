@php
    $commentPage = $commentPage ?? $page ?? $post ?? $article ?? null;
    $isVi = app()->getLocale() === 'vi';
    $replyLabel = $isVi ? 'Trả lời' : 'Replying to';
@endphp

<div class="block static-comments">
    <h2 class="block-title">{{ $isVi ? 'Bình luận' : 'Comments' }}</h2>

    @if($commentPage && $commentPage->comments_enabled)
        @auth
            <form method="POST" action="{{ route('static-pages.comments.store', $commentPage) }}"
                  class="static-comment-form" id="main-comment-form">
                @csrf
                <input type="hidden" name="parent_id" id="reply-parent-id" value="">
                <div id="reply-notice" style="display:none;margin-bottom:6px;font-size:0.88em" class="meta-color">
                    <i class="fa fa-reply"></i>
                    <span id="reply-to-name"></span>
                    &nbsp;
                    <button type="button" onclick="cancelReply()" style="background:none;border:none;cursor:pointer;padding:0;color:inherit;font-size:0.9em;text-decoration:underline">
                        {{ $isVi ? 'Huỷ' : 'Cancel' }}
                    </button>
                </div>
                <textarea name="content" rows="1" required
                          class="sc-input"
                          placeholder="{{ $isVi ? 'Viết bình luận...' : 'Write comment...' }}"
                          id="comment-textarea"
                          onfocus="this.rows=4"></textarea>
                <button class="btn btn-primary btn-sm sc-submit" type="submit">
                    {{ $isVi ? 'Gửi' : 'Post' }}
                </button>
            </form>
        @else
            <div class="sc-input sc-input--placeholder">
                <a href="{{ route('login') }}">{{ $isVi ? 'Đăng nhập' : 'Login' }}</a>
                {{ $isVi ? 'để bình luận.' : 'to comment.' }}
            </div>
        @endauth
    @endif

    <div class="static-comment-list">
        @forelse($comments as $comment)
            <div class="sc-item" id="comment-{{ $comment->id }}">
                <div class="sc-avatar">
                    <img src="{{ optional($comment->user)->avatar ?: asset('static/account/images/no-ava.jpg') }}" alt="">
                </div>
                <div class="sc-body">
                    <div class="sc-meta">
                        <a href="{{ optional($comment->user)->id ? route('users.show.profile', $comment->user) : '#' }}" class="sc-name">
                            {{ optional($comment->user)->name ?? optional($comment->user)->username ?? 'Anonymous' }}
                        </a>
                        <span class="sc-date meta-color">{{ $comment->created_at ? $comment->created_at->format('n/j/Y') : '' }}</span>
                    </div>
                    <div class="sc-content">{{ $comment->content }}</div>

                    <div class="sc-actions">
                        @auth
                            @if($commentPage && $commentPage->comments_enabled)
                                <button type="button" class="sc-action-btn"
                                        onclick="setReply({{ $comment->id }}, {{ json_encode(optional($comment->user)->name ?? optional($comment->user)->username ?? 'Anonymous') }})">
                                    {{ $isVi ? 'Trả lời' : 'Reply' }}
                                </button>
                            @endif
                            @if(auth()->id() === $comment->user_id || auth()->user()?->is_admin)
                                <span class="sc-action-sep">·</span>
                                <form method="POST"
                                      action="{{ route('static-pages.comments.destroy', [$commentPage, $comment]) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('{{ $isVi ? 'Xoá bình luận này?' : 'Delete this comment?' }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="sc-action-btn sc-action-btn--danger">
                                        {{ $isVi ? 'Xoá' : 'Delete' }}
                                    </button>
                                </form>
                            @endif
                        @endauth

                        {{-- Vote --}}
                        <div class="sc-vote">
                            <button class="sc-vote-btn" disabled title="Upvote">▲</button>
                            <span class="sc-vote-score">{{ $comment->score ?? 0 }}</span>
                            <button class="sc-vote-btn" disabled title="Downvote">▼</button>
                        </div>
                    </div>

                    {{-- Replies --}}
                    @foreach($comment->replies as $reply)
                        <div class="sc-item sc-item--reply" id="comment-{{ $reply->id }}">
                            <div class="sc-avatar">
                                <img src="{{ optional($reply->user)->avatar ?: asset('static/account/images/no-ava.jpg') }}" alt="">
                            </div>
                            <div class="sc-body">
                                <div class="sc-meta">
                                    <a href="{{ optional($reply->user)->id ? route('users.show.profile', $reply->user) : '#' }}" class="sc-name">
                                        {{ optional($reply->user)->name ?? optional($reply->user)->username ?? 'Anonymous' }}
                                    </a>
                                    <span class="sc-date meta-color">{{ $reply->created_at ? $reply->created_at->format('n/j/Y') : '' }}</span>
                                </div>
                                <div class="sc-content">{{ $reply->content }}</div>
                                <div class="sc-actions">
                                    @auth
                                        @if(auth()->id() === $reply->user_id || auth()->user()?->is_admin)
                                            <form method="POST"
                                                  action="{{ route('static-pages.comments.destroy', [$commentPage, $reply]) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('{{ $isVi ? 'Xoá bình luận này?' : 'Delete this comment?' }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="sc-action-btn sc-action-btn--danger">
                                                    {{ $isVi ? 'Xoá' : 'Delete' }}
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                    <div class="sc-vote">
                                        <button class="sc-vote-btn" disabled>▲</button>
                                        <span class="sc-vote-score">{{ $reply->score ?? 0 }}</span>
                                        <button class="sc-vote-btn" disabled>▼</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="meta-color" style="padding:12px 0">{{ $isVi ? 'Chưa có bình luận.' : 'No comments yet.' }}</p>
        @endforelse
    </div>

    {{ $comments->links() }}
</div>

@once
@push('scripts')
<script>
var _replyLabel = {{ json_encode($replyLabel) }};
function setReply(commentId, authorName) {
    document.getElementById('reply-parent-id').value = commentId;
    document.getElementById('reply-to-name').textContent = _replyLabel + ': ' + authorName;
    document.getElementById('reply-notice').style.display = 'block';
    var ta = document.getElementById('comment-textarea');
    if (ta) { ta.rows = 4; ta.focus(); }
    document.getElementById('main-comment-form')?.scrollIntoView({behavior:'smooth', block:'center'});
}
function cancelReply() {
    document.getElementById('reply-parent-id').value = '';
    document.getElementById('reply-notice').style.display = 'none';
    document.getElementById('reply-to-name').textContent = '';
}
</script>
@endpush
@endonce
