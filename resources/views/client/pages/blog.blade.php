@extends('layout.novelight')

@section('template_title', $pageTitle ?? 'Blog')

@section('content')
<main class="alpha-blog-page">
    <div class="container">
        <section class="alpha-blog-list">
            @foreach($blogPosts as $post)
                <article class="alpha-blog-card">
                    <div class="alpha-blog-card__body">
                        <div class="alpha-blog-card__meta">
                            <span class="alpha-blog-author">
                                <img src="{{ $post['author_avatar'] ?? '/static/core/images/alphanovel/editor-evelyn-mitchell.png' }}" alt="{{ $post['author'] }}" loading="lazy">
                                {{ $post['author'] }}
                            </span>
                            <time datetime="{{ optional($post['date'])->toDateString() }}">{{ optional($post['date'])->format('F j, Y') }}</time>
                        </div>

                        <a href="{{ $post['url'] }}" class="alpha-blog-card__title">{{ $post['title'] }}</a>
                        <p>{{ $post['excerpt'] }}</p>

                        <div class="alpha-blog-card__actions">
                            <a href="{{ $post['url'] }}" class="alpha-blog-card__read">Read</a>
                            <span>{{ $post['read_time'] }} min read</span>
                            <button type="button" class="alpha-blog-share" data-url="{{ $post['url'] }}">Share</button>
                        </div>
                    </div>

                    <a href="{{ $post['url'] }}" class="alpha-blog-card__media">
                        <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" loading="lazy">
                    </a>
                </article>
            @endforeach
        </section>

        <nav class="alpha-blog-pagination" aria-label="Blog pagination">
            <span class="active">1</span>
            <span>2</span>
            <span>3</span>
            <span>4</span>
            <span>5</span>
        </nav>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function (event) {
    var share = event.target.closest('.alpha-blog-share');
    if (!share) return;
    var url = share.getAttribute('data-url') || window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url);
    }
});
</script>
@endpush
