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

        {{-- Hàng lọc thể loại: bấm cate nào thì lọc kết quả VÀ đổi Top Tags theo cate đó. --}}
        <div class="alpha-search-genres" id="alpha-search-genres">
            <button type="button" class="alpha-search-genre{{ $activeGenre ? '' : ' is-active' }}" data-genre="">All</button>
            @foreach($filterGenres as $genre)
                <button type="button"
                        class="alpha-search-genre{{ $activeGenre && $activeGenre->id === $genre->id ? ' is-active' : '' }}"
                        data-genre="{{ $genre->id }}">{{ $genre->name }}</button>
            @endforeach
        </div>

        <div id="alpha-search-tags">
            @include('client.home.partials.search-tags')
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

    var input  = form.querySelector('input[name="keyword"]');
    var tags   = document.getElementById('alpha-search-tags');
    var genres = document.getElementById('alpha-search-genres');
    var genre  = new URLSearchParams(location.search).get('genre') || '';
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
        var url = form.action + '?keyword=' + encodeURIComponent(q)
                + (genre ? '&genre=' + encodeURIComponent(genre) : '');

        results.classList.add('is-loading');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                results.innerHTML = data.results;
                if (tags && data.tags) tags.innerHTML = data.tags;
                results.classList.remove('is-loading');
                // Giữ URL khớp nội dung để F5 hoặc chia sẻ link vẫn đúng.
                window.history.replaceState({}, '', (q || genre) ? url : form.action);
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

    // Chọn thể loại -> lọc luôn, không cần chờ debounce.
    if (genres) {
        genres.addEventListener('click', function (e) {
            var chip = e.target.closest('.alpha-search-genre');
            if (!chip) return;
            genre = chip.dataset.genre || '';
            genres.querySelectorAll('.alpha-search-genre').forEach(function (c) {
                c.classList.toggle('is-active', c === chip);
            });
            clearTimeout(timer);
            run();
        });
    }

    // Phân trang cũng nạp bằng AJAX thay vì tải lại cả trang.
    results.addEventListener('click', function (e) {
        var link = e.target.closest('.alpha-pagination a, .pagination a');
        if (!link || !link.href) return;
        e.preventDefault();
        if (controller) controller.abort();
        controller = new AbortController();
        results.classList.add('is-loading');
        fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                results.innerHTML = data.results;
                if (tags && data.tags) tags.innerHTML = data.tags;
                results.classList.remove('is-loading');
                window.history.replaceState({}, '', link.href);
                results.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })
            .catch(function (err) {
                if (err.name !== 'AbortError') results.classList.remove('is-loading');
            });
    });

    syncButton();
})();
</script>
@endpush
