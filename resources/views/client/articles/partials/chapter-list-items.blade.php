@foreach($chapters as $chapter)
    @php
        $chapterCreditCost = $chapter->getEffectiveCreditCost($article);
        $chapterIsPaid = $chapterCreditCost > 0;
        $chapterIsUnlocked = $chapterIsPaid && (($unlockedChapterIds ?? collect())->contains($chapter->id) || ($hasActiveVip ?? false));
    @endphp
    <a href="{{ route('articles.chapters.show', [$article, $chapter->number]) }}" class="chapter ">
        <div class="title">
            {{ __('messages.article.chapter') }} {{ $chapter->number }} - <span>{{ $chapter->title }}</span>
        </div>
        <div class="chapter-info">
            @if($chapterIsPaid)
                @guest
                    <span class="cost"><i class="fa fa-lock"></i></span>
                @else
                    @if($chapterIsUnlocked)
                        <span class="cost paid">paid</span>
                    @else
                        <span class="cost"><i class="fa fa-money-bill"></i> {{ number_format($chapterCreditCost) }}</span>
                    @endif
                @endguest
            @endif
            <span class="author"><i class="fa fa-eye"></i> {{ number_format($chapter->view) }}</span>
            <span class="date">{{ optional($chapter->created_at)->format('d.m.Y') }}</span>
        </div>
    </a>
@endforeach
