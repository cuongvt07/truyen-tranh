@extends('layout.novelight')

@section('template_title', $article->title . ' Reviews')
@section('meta_description', 'Reader reviews for ' . $article->title)

@section('content')
@php
    $primaryGenre = $article->genres->first();
@endphp

<main class="alpha-review-page">
    <nav class="alpha-book-breadcrumb">
        <a href="{{ route_path('catalog.index', []) }}">{{ __('messages.i18n.novels') }}</a>
        @if($primaryGenre)
            <span>/</span>
            <a href="{{ route_path('genres.show', $primaryGenre) }}">{{ $primaryGenre->name }}</a>
        @endif
        <span>/</span>
        <a href="{{ route_path('articles.show', $article) }}">{{ $article->title }}</a>
        <span>/</span>
        <span>Reviews</span>
    </nav>

    <section class="alpha-review-board">
        <header class="alpha-review-board__header">
            <h1>Reviews</h1>
            <a href="{{ route_path('articles.reviews', $article) }}" class="alpha-review-board__back">{{ __('messages.i18n.see_all') }}</a>
        </header>

        <div class="alpha-review-grid">
            @forelse($comments as $comment)
                @php
                    $user = $comment->user;
                    $avatar = asset('static/core/images/alphanovel/default-avatar.jpg');
                    $name = optional($user)->name ?? optional($user)->username ?? __('messages.comments.anonymous');
                    $statusLabel = $article->is_completed ? 'Review after the novel completion' : 'Review after half of the novel';
                @endphp
                <article class="alpha-review-page-card">
                    <header class="alpha-review-page-card__head">
                        <div class="alpha-review-page-card__user">
                            <span class="alpha-review-page-card__avatar">
                                <img src="{{ $avatar }}" alt="{{ $name }}" loading="lazy">
                            </span>
                            <span>
                                <strong>{{ $name }}</strong>
                                <small>{{ $statusLabel }}</small>
                            </span>
                        </div>
                        <button type="button" class="alpha-review-report-trigger" data-comment="{{ $comment->id }}" aria-label="Report review">
                            <i class="fa fa-shield-alt"></i>
                        </button>
                    </header>

                    <p class="alpha-review-page-card__text clamp clamp-5">{{ $comment->content }}</p>

                    <div class="alpha-review-page-card__foot">
                        <a href="{{ route_path('articles.reviews.show', [$article, $comment]) }}" class="alpha-review-page-card__more">more</a>
                    </div>
                    <span id="review-{{ $comment->id }}"></span>
                </article>
            @empty
                <div class="alpha-reviews-empty">
                    <i class="fa fa-comment-dots"></i>
                    <strong>No reviews yet</strong>
                    <span>{{ __('messages.article.no_comments') }}</span>
                </div>
            @endforelse
        </div>

        <div class="alpha-pagination">
            {{ $comments->withQueryString()->links('vendor.pagination.novelight') }}
        </div>
    </section>
</main>

<div class="alpha-review-modal" id="alpha-review-report-modal" hidden>
    <div class="alpha-review-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="alpha-review-report-title">
        <button type="button" class="alpha-review-modal__close" aria-label="Close"><i class="fa fa-times"></i></button>
        <div class="alpha-review-modal__icon">!</div>
        <h2 id="alpha-review-report-title">Report an inappropriate review</h2>
        <p>Please describe exactly what you want to complain about in your feedback and include details so that we can process it faster. Thank you for your vigilance.</p>
        <textarea id="alpha-review-report-reason" placeholder="Please describe the reason why you are filing a complaint."></textarea>
        <div class="alpha-review-modal__actions">
            <button type="button" class="alpha-review-modal__cancel">Cancel</button>
            <button type="button" class="alpha-review-modal__submit" disabled>Send Request</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('alpha-review-report-modal');
    if (!modal) return;
    var textarea = document.getElementById('alpha-review-report-reason');
    var submit = modal.querySelector('.alpha-review-modal__submit');
    var currentComment = null;

    function openModal(commentId) {
        currentComment = commentId;
        textarea.value = '';
        submit.disabled = true;
        modal.hidden = false;
        textarea.focus();
    }

    function closeModal() {
        modal.hidden = true;
        currentComment = null;
    }

    document.addEventListener('click', function (event) {
        var report = event.target.closest('.alpha-review-report-trigger');
        if (report) {
            openModal(report.getAttribute('data-comment'));
            return;
        }

        if (event.target === modal || event.target.closest('.alpha-review-modal__close') || event.target.closest('.alpha-review-modal__cancel')) {
            closeModal();
        }
    });

    textarea.addEventListener('input', function () {
        submit.disabled = textarea.value.trim().length < 3;
    });

    submit.addEventListener('click', function () {
        if (!currentComment || submit.disabled) return;
        submit.disabled = true;
        fetch('/comments/' + currentComment + '/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ reason: textarea.value.trim() })
        }).then(function (response) {
            if (response.status === 401) {
                window.location.href = @json(route_path('login', []));
                return null;
            }
            return response.json();
        }).then(function (data) {
            if (!data) return;
            alert(data.message || 'Your report has been sent.');
            closeModal();
        }).catch(function () {
            alert('Could not send the report. Please try again.');
            submit.disabled = false;
        });
    });
})();
</script>
@endpush
