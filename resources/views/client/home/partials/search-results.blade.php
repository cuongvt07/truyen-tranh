        @forelse($articles as $article)
            @php
                $author = optional($article->authors->first())->name ?? 'Updating';
                $readUrl = route_path('articles.show', $article);
                $currentBookmark = $article->relationLoaded('bookmarks') ? $article->bookmarks->first() : null;
                $isFollowed = (bool) $currentBookmark;
            @endphp
            <article class="alpha-search-card">
                <a href="{{ route_path('articles.show', $article) }}" class="alpha-search-card__cover">
                    <img src="{{ novel_poster($article) }}" alt="{{ $article->title }}" loading="lazy">
                    @if(($loop->index % 3) === 0)<em>Recommended</em>@endif
                    <strong>{{ $article->is_completed ? 'Completed' : 'Updated' }}</strong>
                </a>

                <div class="alpha-search-card__body">
                    <a href="{{ route_path('articles.show', $article) }}" class="alpha-search-card__title">
                        {{ $article->title }}
                    </a>
                    <div class="alpha-search-card__meta">
                        <span>Author: <b>{{ $author }}</b></span>
                        <span>Status: <b>{{ $article->is_completed ? __('messages.ui.status_completed') : __('messages.ui.status_ongoing') }}</b></span>
                        <span>Age Rating: <b>{{ $article->is_adult ? '18+' : '16+' }}</b></span>
                    </div>
                    <div class="alpha-search-card__stats">
                        <span><i class="fa fa-eye"></i> {{ $formatCompact($article->view ?? 0) }}</span>
                        <span><i class="fa fa-star"></i> {{ number_format($article->rating ?? 0, 1) }}</span>
                    </div>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($article->description), 270) }}</p>
                    <a href="{{ route_path('articles.show', $article) }}" class="alpha-search-more">more</a>
                </div>

                <div class="alpha-search-card__actions">
                    @auth
                        <form method="POST" action="{{ route_path('articles.bookmarks.store', $article->id) }}" class="alpha-search-bookmark-form">
                            @csrf
                            <input type="hidden" name="name" value="{{ $article->title }} #{{ $article->id }}">
                            <input type="hidden" name="status" value="{{ $isFollowed ? 'remove' : 'reading' }}">
                            <button type="submit" class="alpha-search-bookmark {{ $isFollowed ? 'is-followed' : '' }}" aria-label="{{ $isFollowed ? 'Remove from library' : 'Add to library' }}">
                                <i class="fa fa-heart"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route_path('login') }}" class="alpha-search-bookmark" aria-label="Bookmark">
                            <i class="fa fa-heart"></i>
                        </a>
                    @endauth
                    <a href="{{ $readUrl }}" class="alpha-search-start">Start Reading</a>
                </div>
            </article>
        @empty
            <div class="alpha-search-empty">
                No novels found.
            </div>
        @endforelse

        <div class="alpha-pagination">
            {{ $articles->links('vendor.pagination.novelight') }}
        </div>
