@php
    $activeStatus = $currentListStatus ?? null;
    $wantThis = $wantThisMode ?? false;
    $defaultLabel = $wantThis ? __('messages.article.want_this') : __('messages.article.add_to_list');
    $buttonText = $activeStatus ? __('messages.article.reading') : $defaultLabel;
    $toggleStatus = $activeStatus ? 'remove' : 'reading';
@endphp

@once
<style>
.add-to-list-wrap { position: relative; width: 100%; }
.chapter-info-end .add-to-list-wrap { width: 174px; }
.add-to-list-wrap .btn-add-to-list {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}
.add-to-list-wrap .btn-add-to-list .btn-list {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    align-self: stretch;
    margin: -10px -15px -10px 10px;
    background: #ffefef47;
}
.btn-add-to-list.want-this { background: #e7f3ff; color: #0a6ebd; border: 1px solid #b6dcff; }
.btn-add-to-list.want-this .btn-list { background: #d3e9ff; color: #0a6ebd; }
.btn-add-to-list.want-this:hover { background: #d3e9ff; }
.want-this-count { margin-top: 6px; font-size: 13px; color: var(--meta-color, #888); text-align: center; }
.want-this-count .fa-heart { color: #ff6b81; margin-right: 4px; }
</style>
@endonce

@auth
    <div class="add-to-list-wrap">
        <form method="POST" action="{{ route_path('articles.bookmarks.store', $article->id) }}">
            @csrf
            <input type="hidden" name="name" value="{{ $article->title }} #{{ $article->id }}">
            <input type="hidden" name="status" value="{{ $toggleStatus }}">
            <button type="submit" class="btn btn-add-to-list direct-library-toggle {{ $activeStatus ? 'active' : '' }} {{ $wantThis && !$activeStatus ? 'want-this' : '' }}" aria-pressed="{{ $activeStatus ? 'true' : 'false' }}" title="{{ $activeStatus ? __('messages.chapter.bookmarked') : __('messages.chapter.bookmark') }}">
                <span></span>
                <span class="text-add-to-list">{{ $buttonText }}</span>
                <span class="btn-list"><i class="fa fa-heart"></i></span>
            </button>
        </form>
        @if($wantThis && isset($interestCount))
            <div class="want-this-count"><i class="fa fa-heart"></i> {{ __('messages.article.interested_count', ['count' => number_format($interestCount)]) }}</div>
        @endif
    </div>
@else
    <a href="{{ route_path('login') }}" class="btn btn-add-to-list direct-library-toggle {{ $wantThis ? 'want-this' : '' }}" aria-pressed="false">
        <span></span>
        <span class="text-add-to-list">{{ $defaultLabel }}</span>
        <span class="btn-list"><i class="fa fa-heart"></i></span>
    </a>
    @if($wantThis && isset($interestCount))
        <div class="want-this-count"><i class="fa fa-heart"></i> {{ __('messages.article.interested_count', ['count' => number_format($interestCount)]) }}</div>
    @endif
@endauth
