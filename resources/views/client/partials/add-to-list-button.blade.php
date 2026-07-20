@php
    $statuses = [
        'reading' => __('messages.article.list_reading'),
        'planning' => __('messages.article.list_planning'),
        'dropped' => __('messages.article.list_dropped'),
        'completed' => __('messages.article.list_completed'),
        'paused' => __('messages.article.list_paused'),
    ];

    $activeStatus = $currentListStatus ?? (($hasStartedReading ?? false) ? 'reading' : null);
    // Truyện user gửi / chưa có chương -> nhãn "I want this" (xanh nhẹ) thay cho "Add to list".
    $wantThis = $wantThisMode ?? false;
    $defaultLabel = $wantThis ? __('messages.article.want_this') : __('messages.article.add_to_list');
    $buttonText = $activeStatus ? ($statuses[$activeStatus] ?? __('messages.article.reading')) : $defaultLabel;
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
/* In-wrap positioning for the CSS hover fallback (when Tippy is not active).
   Tippy relocates .add-to-list__content to <body>, so item styles below are
   scoped to .add-to-list__content itself, not to .add-to-list-wrap. */
.add-to-list-wrap .add-to-list__content {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 1100;
    display: none;
}
.add-to-list-wrap:hover .add-to-list__content,
.add-to-list-wrap:focus-within .add-to-list__content { display: block; }

/* The dropdown panel — travels with the element when Tippy moves it. */
.add-to-list__content {
    min-width: 210px;
    padding: 6px;
    border-radius: 8px;
    background: var(--card-bg, #fff);
    border: 1px solid var(--border, #d9dee7);
    box-shadow: 0 10px 28px rgba(0, 0, 0, .18);
}
.add-to-list__content form { margin: 0; }

/* Items: borderless, full-width text rows. appearance:none kills the default
   <button> frame that page CSS would otherwise leave visible. */
.add-to-list__content .add-to-list-btn {
    display: block;
    width: 100%;
    margin: 0;
    padding: 9px 10px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: inherit;
    text-align: left;
    cursor: pointer;
    font: inherit;
    line-height: 1.35;
    -webkit-appearance: none;
    appearance: none;
}
.add-to-list__content .add-to-list-btn:hover,
.add-to-list__content .add-to-list-btn.active { background: var(--bg-soft, #eef0f4); }
.add-to-list__content .add-to-list-remove { color: #dc2626; }
.add-to-list__content hr {
    margin: 6px 0;
    border: 0;
    border-top: 1px solid var(--border, #d9dee7);
}

/* When opened through Tippy, hide Tippy's own dark default box/arrow so only
   our white panel shows. Scoped via :has() so other Tippy menus are untouched. */
.tippy-box[data-theme~="light"]:has(.add-to-list__content) {
    background-color: transparent;
    box-shadow: none;
}
.tippy-box[data-theme~="light"]:has(.add-to-list__content) .tippy-content { padding: 0; }
.tippy-box[data-theme~="light"]:has(.add-to-list__content) .tippy-arrow { display: none; }

/* "I want this" — truyện user gửi / chưa có chương: nút xanh nhẹ + số người quan tâm */
.btn-add-to-list.want-this { background: #e7f3ff; color: #0a6ebd; border: 1px solid #b6dcff; }
.btn-add-to-list.want-this .btn-list { background: #d3e9ff; color: #0a6ebd; }
.btn-add-to-list.want-this:hover { background: #d3e9ff; }
.want-this-count { margin-top: 6px; font-size: 13px; color: var(--meta-color, #888); text-align: center; }
.want-this-count .fa-heart { color: #ff6b81; margin-right: 4px; }
</style>
@endonce

@auth
    <div class="add-to-list-wrap">
        <button type="button" class="btn btn-add-to-list {{ $wantThis && !$activeStatus ? 'want-this' : '' }}" aria-expanded="{{ $activeStatus ? 'true' : 'false' }}">
            <span></span>
            <span class="text-add-to-list">{{ $buttonText }}</span>
            <span class="btn-list"><i class="fa fa-list"></i></span>
        </button>
        @if($wantThis && isset($interestCount))
            <div class="want-this-count"><i class="fa fa-heart"></i> {{ __('messages.article.interested_count', ['count' => number_format($interestCount)]) }}</div>
        @endif

        <div class="tinny-content__inner add-to-list__content">
            @foreach($statuses as $status => $label)
                <form method="POST" action="{{ route_path('articles.bookmarks.store', $article->id) }}">
                    @csrf
                    <input type="hidden" name="name" value="{{ $article->title }} #{{ $article->id }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button type="submit" class="add-to-list-btn {{ $activeStatus === $status ? 'active' : '' }}" data-list="{{ $status }}">
                        {{ $label }}
                    </button>
                </form>
            @endforeach

            <hr>
            <form method="POST" action="{{ route_path('articles.bookmarks.store', $article->id) }}">
                @csrf
                <input type="hidden" name="status" value="remove">
                <button type="submit" class="add-to-list-btn add-to-list-remove" data-list="remove">
                    {{ __('messages.article.remove_from_list') }}
                </button>
            </form>
        </div>
    </div>
@else
    <a href="{{ route_path('login') }}" class="btn btn-add-to-list {{ $wantThis ? 'want-this' : '' }}" aria-expanded="false">
        <span></span>
        <span class="text-add-to-list">{{ $defaultLabel }}</span>
        <span class="btn-list"><i class="fa fa-list"></i></span>
    </a>
    @if($wantThis && isset($interestCount))
        <div class="want-this-count"><i class="fa fa-heart"></i> {{ __('messages.article.interested_count', ['count' => number_format($interestCount)]) }}</div>
    @endif
@endauth
