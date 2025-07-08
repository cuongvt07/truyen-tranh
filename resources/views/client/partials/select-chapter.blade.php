@php
    $previousChapter = $chapter->previous;
    $nextChapter = $chapter->next;
@endphp

<div class="btn-group">
    @if ($previousChapter)
        <a class="btn btn-success btn-chapter-nav" id="prev_chap"
           href="{{ route('articles.chapters.show', [$article->id, $previousChapter->number]) }}">
            <span class="glyphicon glyphicon-chevron-left"></span> Trước
        </a>
    @else
        <a class="btn btn-success btn-chapter-nav disabled" href="javascript:void(0)">
            <span class="glyphicon glyphicon-chevron-left"></span> Trước
        </a>
    @endif

    <button type="button" class="btn btn-success btn-chapter-nav chapter_jump">
        <span class="glyphicon glyphicon-list-alt"></span>
    </button>
    <select class="btn btn-success btn-chapter-nav form-control chapter_jump"
            onchange="window.location.href='/articles/{{ $article->id }}/chapters/'+this.value;">
        @foreach ($articleChapters as $articleChapter)
            <option value="{{ $articleChapter->number }}" {{ $articleChapter->number == $chapter->number ? 'selected' : '' }}>
                {{ $articleChapter->number_text . ': ' . $articleChapter->title }}
            </option>
        @endforeach
    </select>

    @if ($nextChapter)
        <a class="btn btn-success btn-chapter-nav" id="next_chap"
           href="{{ route('articles.chapters.show', [$article->id, $nextChapter->number]) }}"
           data-affiliate-link="{{ $redirectAffiliateLink ?? '' }}">
            Tiếp <span class="glyphicon glyphicon-chevron-right"></span>
        </a>
    @else
        <a class="btn btn-success btn-chapter-nav disabled" href="javascript:void(0)">
            Tiếp <span class="glyphicon glyphicon-chevron-right"></span>
        </a>
    @endif
</div>
