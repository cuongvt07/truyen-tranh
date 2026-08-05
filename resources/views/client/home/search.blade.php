@extends('layout.novelight')

@section('template_title', $title ?? 'Search')
@section('meta_description', $description ?? 'Search novels')

@php
    $formatCompact = function ($value) {
        $value = (int) $value;
        if ($value >= 1000000) return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'M';
        if ($value >= 1000) return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'K';
        return number_format($value);
    };
@endphp

@section('content')
<main class="alpha-search-page">
    <section class="alpha-search-panel">
        <form action="{{ route_path('home.search') }}" method="GET" class="alpha-search-form" id="alpha-search-form">
            <input
                type="search"
                name="keyword"
                value="{{ $keyword }}"
                placeholder="Type novel title or tag..."
                autocomplete="off"
            >
            {{-- Nút đổi vai theo ô nhập: trống = biểu tượng tìm (mờ),
                 có chữ = dấu X để xoá (sáng). --}}
            <button type="submit" id="alpha-search-action" class="is-empty" aria-label="Search">
                <i class="fa fa-search"></i>
            </button>
        </form>

        <h1>Top Tags</h1>
        <div class="alpha-search-tags">
            @forelse($topTags as $tag)
                <a href="{{ route_path('home.search', ['keyword' => $tag->name]) }}">{{ $tag->name }}</a>
            @empty
                <span>No tags yet.</span>
            @endforelse
        </div>
    </section>

    <section class="alpha-search-results" id="alpha-search-results">
        @include('client.home.partials.search-results')
    </section>
</main>
@endsection

@push('scripts')
<script>
// Tìm kiếm AJAX: gõ tới đâu nạp kết quả tới đó, có debounce để không bắn
// request theo từng phím. Nút bên phải đổi vai: trống thì là kính lúp (mờ),
// có chữ thì thành dấu X để xoá.
(function () {
    var form    = document.getElementById('alpha-search-form');
    var results = document.getElementById('alpha-search-results');
    var action  = document.getElementById('alpha-search-action');
    if (!form || !results || !action) return;

    var input = form.querySelector('input[name="keyword"]');
    var timer = null;
    var controller = null;
    var DEBOUNCE_MS = 350;

    function syncButton() {
        var has = input.value.trim() !== '';
        action.classList.toggle('is-empty', !has);
        action.innerHTML = has ? '<i class="fa fa-times"></i>' : '<i class="fa fa-search"></i>';
        action.setAttribute('aria-label', has ? 'Clear' : 'Search');
    }

    function run() {
        // Huỷ request cũ để kết quả về trễ không ghi đè kết quả mới.
        if (controller) controller.abort();
        controller = new AbortController();

        var q = input.value.trim();
        var url = form.action + '?keyword=' + encodeURIComponent(q);

        results.classList.add('is-loading');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                results.innerHTML = html;
                results.classList.remove('is-loading');
                // Giữ URL khớp nội dung để F5 hoặc chia sẻ link vẫn đúng.
                window.history.replaceState({}, '', q ? url : form.action);
            })
            .catch(function (e) {
                if (e.name !== 'AbortError') results.classList.remove('is-loading');
            });
    }

    input.addEventListener('input', function () {
        syncButton();
        clearTimeout(timer);
        timer = setTimeout(run, DEBOUNCE_MS);
    });

    action.addEventListener('click', function (e) {
        if (input.value.trim() === '') return;   // ô trống -> để form submit như thường
        e.preventDefault();
        input.value = '';
        syncButton();
        input.focus();
        run();
    });

    // Enter không reload cả trang nữa.
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        run();
    });

    syncButton();
})();
</script>
@endpush
