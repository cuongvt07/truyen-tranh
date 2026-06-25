@php
    // Tên người đăng (poster) lấy theo người sở hữu truyện — tính 1 lần, dùng lại mọi dòng.
    $posterName = ($showPoster ?? false)
        ? (optional($article->user)->name ?: optional($article->user)->username)
        : null;
@endphp
@foreach($chapters as $chapter)
    @php
        $chapterCreditCost = $chapter->getEffectiveCreditCost($article);
        $chapterIsPaid = $chapterCreditCost > 0;
        $chapterIsUnlocked = $chapterIsPaid && ($unlockedChapterIds ?? collect())->contains($chapter->id);
    @endphp
    <a href="{{ route('articles.chapters.show', [$article, $chapter->number]) }}"
       class="chapter {{ ($currentChapterId ?? null) === $chapter->id ? 'active' : '' }}">
        <div class="title">
            {{ __('messages.article.chapter') }} {{ $chapter->number }}@unless($compactTitle ?? false) - <span>{{ $chapter->title }}</span>@endunless
        </div>
        <div class="chapter-info">
            @if($chapterIsPaid)
                @guest
                    <span class="cost"><i class="fa fa-lock"></i></span>
                @else
                    @if($chapterIsUnlocked)
                        <span class="cost paid">{{ __('messages.article.paid') }}</span>
                    @else
                        <span class="cost"><i class="fa fa-money-bill"></i> {{ number_format($chapterCreditCost) }}</span>
                    @endif
                @endguest
            @endif
            <span class="author"><i class="fa fa-eye"></i> {{ number_format($chapter->view) }}</span>
            @if($posterName)
                <span class="poster"><i class="fa fa-user"></i> {{ $posterName }}</span>
            @endif
            <span class="date">{{ optional($chapter->created_at)->format('d.m.Y') }}</span>
        </div>
    </a>
@endforeach
