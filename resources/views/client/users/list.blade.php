@extends('client.users.profile')
@section('template_title', __('messages.account.account_title', ['name' => $user->username]))

@section('user_content')
@php
    // Thứ tự trạng thái hiển thị (khớp thanh lọc trong thiết kế)
    $statusOrder = ['reading', 'completed', 'planning', 'paused', 'dropped'];
    $statusLabels = [
        'reading'   => __('messages.article.list_reading'),
        'completed' => __('messages.article.list_completed'),
        'planning'  => __('messages.article.list_planning'),
        'paused'    => __('messages.article.list_paused'),
        'dropped'   => __('messages.article.list_dropped'),
    ];
    // Trạng thái không hợp lệ/null -> coi như 'reading'
    $grouped = $bookmarks->groupBy(fn ($b) => in_array($b->status, $statusOrder, true) ? $b->status : 'reading');
@endphp

<div class="block list-names reading-list-filter">
    <button type="button" class="btn" data-filter="all">{{ __('messages.account.filter_all') }}</button>
    @foreach($statusOrder as $st)
        <button type="button" class="btn btn-invincible" data-filter="{{ $st }}">{{ $statusLabels[$st] }}</button>
    @endforeach
</div>

<div id="reading-list" data-mine="{{ $isMine ? 1 : 0 }}">
    @foreach($statusOrder as $st)
        <section class="rl-section" data-status="{{ $st }}">
            <h3 class="rl-heading">{{ $statusLabels[$st] }}</h3>
            <div class="rl-cards">
                @foreach(($grouped[$st] ?? []) as $bm)
                    @php
                        $article = $bm->article;
                        $continue = $continueMap[$article->id] ?? optional($article->newest_chapter)->number;
                    @endphp
                    <div class="rl-card block" data-status="{{ $st }}"
                         data-store-url="{{ route('articles.bookmarks.store', $article->id) }}">
                        <a href="{{ route('articles.show', $article) }}" class="rl-poster image image-cover lazy-load-bg">
                            <img class="lazy-image" loading="lazy" src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                        </a>
                        <div class="rl-info">
                            <a href="{{ route('articles.show', $article) }}" class="rl-title clamp clamp-2">{{ $article->title }}</a>
                            @if($continue)
                                <a href="{{ route('articles.chapters.show', [$article, $continue]) }}" class="rl-continue">
                                    {{ __('messages.article.continue_reading') }} ({{ __('messages.account.chapter_number', ['number' => $continue]) }})
                                </a>
                            @endif
                        </div>
                        @if($isMine)
                            <div class="rl-menu">
                                <button type="button" class="rl-menu-btn btn btn-invincible" aria-label="menu">&hellip;</button>
                                <div class="rl-menu-pop">
                                    @foreach($statusOrder as $s)
                                        <button type="button" class="rl-menu-item rl-status {{ $s === $st ? 'active' : '' }}" data-status="{{ $s }}">{{ $statusLabels[$s] }}</button>
                                    @endforeach
                                    <hr>
                                    <button type="button" class="rl-menu-item rl-remove" data-status="remove">{{ __('messages.article.remove_from_list') }}</button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    <div class="block rl-empty" @if(!$bookmarks->isEmpty()) style="display:none" @endif>
        <div class="nothing" style="padding:40px 0;text-align:center;color:var(--meta-color)">
            <i class="fa fa-list" style="font-size:32px;opacity:.4;display:block;margin-bottom:10px"></i>
            {{ __('messages.account.bookmarks_empty') }}
        </div>
    </div>
</div>

<style>
.reading-list-filter { position:sticky; top:0; z-index:5; }
#reading-list .rl-heading { font-size:18px; margin:18px 0 8px; }
#reading-list .rl-section[data-empty="1"] { display:none; }
#reading-list .rl-card {
    display:flex; align-items:center; gap:14px;
    padding:12px 14px; margin-bottom:10px; position:relative;
}
#reading-list .rl-poster { width:48px; height:64px; border-radius:4px; overflow:hidden; flex-shrink:0; display:block; }
#reading-list .rl-poster img { width:100%; height:100%; object-fit:cover; }
#reading-list .rl-info { flex:1; min-width:0; display:flex; flex-direction:column; gap:2px; }
#reading-list .rl-title { font-weight:600; color:var(--text-color); text-decoration:none; }
#reading-list .rl-title:hover { color:var(--color-site, inherit); }
#reading-list .rl-continue { font-size:13px; color:var(--meta-color); text-decoration:none; }
#reading-list .rl-continue:hover { text-decoration:underline; }
/* Menu ... */
#reading-list .rl-menu { position:relative; flex-shrink:0; }
#reading-list .rl-menu-btn { min-width:40px; font-size:18px; line-height:1; padding:4px 10px; }
#reading-list .rl-menu-pop {
    display:none; position:absolute; right:0; top:calc(100% + 4px); z-index:20;
    min-width:180px; padding:6px; border-radius:8px;
    background:var(--card-bg, #fff); border:1px solid var(--border, #d9dee7);
    box-shadow:0 10px 28px rgba(0,0,0,.18);
}
#reading-list .rl-menu.open .rl-menu-pop { display:block; }
#reading-list .rl-menu-item {
    display:block; width:100%; text-align:left; padding:9px 10px; border:0;
    background:transparent; color:inherit; cursor:pointer; font:inherit; border-radius:6px;
}
#reading-list .rl-menu-item:hover, #reading-list .rl-menu-item.active { background:var(--bg-soft, #eef0f4); }
#reading-list .rl-menu-item.rl-remove { color:#dc2626; }
#reading-list .rl-menu-pop hr { margin:6px 0; border:0; border-top:1px solid var(--border, #d9dee7); }
@media only screen and (max-width:600px) {
    #reading-list .rl-card { gap:10px; padding:10px; }
    #reading-list .rl-poster { width:42px; height:56px; }
}
</style>

<script>
(function () {
    var root = document.getElementById('reading-list');
    if (!root) return;
    var isMine = root.getAttribute('data-mine') === '1';
    var CSRF = @json(csrf_token());
    var currentFilter = 'all';

    function refresh() {
        root.querySelectorAll('.rl-section').forEach(function (s) {
            s.setAttribute('data-empty', s.querySelector('.rl-card') ? '0' : '1');
        });
        applyFilter(currentFilter);
        var anyCard = root.querySelector('.rl-card');
        var empty = root.querySelector('.rl-empty');
        if (empty) empty.style.display = anyCard ? 'none' : '';
    }

    function applyFilter(f) {
        currentFilter = f;
        root.querySelectorAll('.rl-section').forEach(function (s) {
            var hasCards = s.getAttribute('data-empty') === '0';
            var match = (f === 'all' || s.getAttribute('data-status') === f);
            s.style.display = (hasCards && match) ? '' : 'none';
        });
    }

    // Thanh lọc trạng thái (client-side)
    document.querySelectorAll('.reading-list-filter .btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.reading-list-filter .btn').forEach(function (b) { b.classList.add('btn-invincible'); });
            btn.classList.remove('btn-invincible');
            applyFilter(btn.getAttribute('data-filter'));
        });
    });

    if (isMine) {
        // Mở/đóng menu ...
        root.addEventListener('click', function (e) {
            var menuBtn = e.target.closest('.rl-menu-btn');
            if (menuBtn) {
                var menu = menuBtn.parentElement;
                var wasOpen = menu.classList.contains('open');
                root.querySelectorAll('.rl-menu.open').forEach(function (m) { m.classList.remove('open'); });
                if (!wasOpen) menu.classList.add('open');
                e.stopPropagation();
                return;
            }
            var item = e.target.closest('.rl-menu-item');
            if (item) { changeStatus(item); return; }
        });
        document.addEventListener('click', function () {
            root.querySelectorAll('.rl-menu.open').forEach(function (m) { m.classList.remove('open'); });
        });

        function changeStatus(item) {
            var card = item.closest('.rl-card');
            var status = item.getAttribute('data-status');
            if (!card) return;
            card.querySelector('.rl-menu').classList.remove('open');
            item.style.pointerEvents = 'none';
            fetch(card.getAttribute('data-store-url'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF,
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: 'status=' + encodeURIComponent(status)
            }).then(function (r) { return r.json(); }).then(function (d) {
                if (!d || !d.success) throw new Error('failed');
                if (status === 'remove') {
                    card.remove();
                } else {
                    card.setAttribute('data-status', status);
                    // cập nhật trạng thái active trong menu
                    card.querySelectorAll('.rl-status').forEach(function (b) {
                        b.classList.toggle('active', b.getAttribute('data-status') === status);
                    });
                    var dest = root.querySelector('.rl-section[data-status="' + status + '"] .rl-cards');
                    if (dest) dest.appendChild(card);
                }
                refresh();
            }).catch(function () {
                item.style.pointerEvents = '';
            });
        }
    }

    refresh();
})();
</script>
@endsection

