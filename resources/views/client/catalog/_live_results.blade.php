<div class="searches">
    @if($results->isEmpty())
        <div class="nothing" style="padding:20px;text-align:center;color:var(--meta-color)">
            {{ __('messages.catalog.no_results_for', ['term' => $term]) }}
        </div>
    @else
        <div id="ln-search-results" class="search-results">
            <div class="search-name">{{ __('messages.catalog.results_count', ['count' => $results->count()]) }}</div>
            <div class="search-results__inner">
                @foreach($results as $article)
                    <a href="{{ route('articles.show', $article->id) }}" class="manga-list-item">
                        <div class="image image-cover">
                            <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}">
                        </div>
                        <div class="manga-list__info">
                            <div class="title">{{ $article->title }}</div>
                            <div class="meta-color">
                                {{ $article->is_completed ? __('messages.catalog.status_completed') : __('messages.catalog.status_ongoing') }}
                                @if($article->view) • {{ __('messages.catalog.views_count', ['count' => number_format($article->view)]) }} @endif
                            </div>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('catalog.index', ['search' => $term]) }}" class="manga-list-item" style="justify-content:center;font-weight:600">
                    <div class="manga-list__info" style="text-align:center">
                        <div class="title"><i class="fa fa-search"></i> {{ __('messages.catalog.view_all_results') }}</div>
                    </div>
                </a>
            </div>
        </div>
    @endif
</div>
