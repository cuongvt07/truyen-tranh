@extends('layout.novelight')

@section('template_title', $article->title . ' Review')
@section('meta_description', 'Reader review for ' . $article->title)

@section('content')
@php
    $primaryGenre = $article->genres->first();
    $user = $comment->user;
    $avatar = asset('static/core/images/alphanovel/default-avatar.jpg');
    $name = optional($user)->name ?? optional($user)->username ?? __('messages.comments.anonymous');
    $statusLabel = $article->is_completed ? 'Review after the novel completion' : 'Review after half of the novel';
    $myVote = (int) ($comment->my_vote ?? 0);
@endphp

<main class="alpha-review-page alpha-review-single-page">
    <nav class="alpha-book-breadcrumb">
        <a href="{{ route_path('catalog.index', []) }}">Novels</a>
        @if($primaryGenre)
            <span>/</span>
            <a href="{{ route_path('genres.show', $primaryGenre) }}">{{ $primaryGenre->name }}</a>
        @endif
        <span>/</span>
        <a href="{{ route_path('articles.show', $article) }}">{{ $article->title }}</a>
        <span>/</span>
        <a href="{{ route_path('articles.reviews', $article) }}">Reviews</a>
    </nav>

    <article class="alpha-review-single-card" id="review-{{ $comment->id }}">
        <header class="alpha-review-single-card__head">
            <div class="alpha-review-single-card__user">
                <span class="alpha-review-single-card__avatar">
                    <img src="{{ $avatar }}" alt="{{ $name }}" loading="lazy">
                </span>
                <span>
                    <strong>{{ $name }}</strong>
                    <small>{{ $statusLabel }}</small>
                </span>
            </div>

            <div class="alpha-review-single-card__actions">
                <button type="button" class="alpha-review-single-action alpha-review-single-action--icon alpha-review-share-copy" aria-label="Copy review link">
                    <i class="fa fa-share-alt"></i>
                </button>
                <button type="button" class="alpha-review-single-action alpha-review-single-action--facebook alpha-review-share-facebook">
                    <i class="fab fa-facebook"></i>
                    <span>Share</span>
                </button>
                <button type="button" class="alpha-review-single-action alpha-review-like {{ $myVote === 1 ? 'is-active' : '' }}" data-comment="{{ $comment->id }}" data-vote="1">
                    <i class="far fa-thumbs-up"></i>
                    <span>Like</span>
                </button>
                <button type="button" class="alpha-review-single-action alpha-review-single-action--icon alpha-review-report-trigger" data-comment="{{ $comment->id }}" aria-label="Report review">
                    <i class="fa fa-shield-alt"></i>
                </button>
            </div>
        </header>

        <p class="alpha-review-single-card__content">{{ $comment->content }}</p>

        <time class="alpha-review-single-card__date" datetime="{{ optional($comment->created_at)->toDateString() }}">
            {{ optional($comment->created_at)->format('F j, Y') }}
        </time>
    </article>
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

        var copy = event.target.closest('.alpha-review-share-copy');
        if (copy) {
            if (navigator.clipboard) navigator.clipboard.writeText(window.location.href);
            return;
        }

        var facebook = event.target.closest('.alpha-review-share-facebook');
        if (facebook) {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank', 'noopener,noreferrer,width=720,height=560');
            return;
        }

        var like = event.target.closest('.alpha-review-like');
        if (like) {
            var commentId = like.getAttribute('data-comment');
            fetch('/comments/' + commentId + '/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ value: 1 })
            }).then(function (response) {
                if (response.status === 401) {
                    window.location.href = @json(route_path('login', []));
                    return null;
                }
                return response.json();
            }).then(function (data) {
                if (!data || !data.ok) return;
                like.classList.toggle('is-active', Number(data.myVote) === 1);
            });
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
