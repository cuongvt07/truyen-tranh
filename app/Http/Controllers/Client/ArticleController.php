<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\ChapterUnlock;
use App\Models\Genre;
use App\Models\ReadingHistory;
use App\Models\UserVip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Nette\Utils\Paginator;

class ArticleController extends Controller
{
    private const CHAPTERS_PER_PAGE = 50;

    public function show(Request $request, Article $article)
    {
        if ($request->route()->originalParameter('article') !== $article->getRouteKey()) {
            return redirect()->route('articles.show', $article, 301);
        }

        $article->increaseViewCount();

        $chapterNumbers = $article->chapters()->orderByDesc('number')->pluck('number');
        $chapterPages = $this->buildChapterPages($chapterNumbers, self::CHAPTERS_PER_PAGE);
        $chapters = $article->chapters()
            ->orderByDesc('number')
            ->take(self::CHAPTERS_PER_PAGE)
            ->get();
        $latestChapters = $article->chapters()->orderByDesc('number')->take(10)->get();
        // Chương hẹn giờ (chưa tới giờ đăng) — hiện "Coming soon" trên tab chương, không đọc được.
        $upcomingChapters = $article->chapters()
            ->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class)
            ->whereNotNull('published_at')
            ->where('published_at', '>', now())
            ->orderByDesc('number')
            ->get();
        $comments = $article->getNewestCommentsPaginate();
        $displayChapterIds = $latestChapters->pluck('id')
            ->merge($chapters->pluck('id'))
            ->unique()
            ->values();

        $unlockedChapterIds = collect();
        $hasActiveVip = false;
        $hasStartedReading = false;
        $continueChapterNumber = null;
        $currentListStatus = null;

        if (Auth::check()) {
            $hasActiveVip = UserVip::where('user_id', Auth::id())
                ->where('end_at', '>=', now())
                ->exists();

            $unlockedChapterIds = ChapterUnlock::where('user_id', Auth::id())
                ->whereIn('chapter_id', $displayChapterIds)
                ->pluck('chapter_id');

            $lastHistory = ReadingHistory::where('user_id', Auth::id())
                ->where('article_id', $article->id)
                ->latest('read_at')
                ->first();

            $hasStartedReading = $lastHistory !== null;
            $continueChapterNumber = $lastHistory?->chapter_number;

            $currentListStatus = Bookmark::where('user_id', Auth::id())
                ->where('article_id', $article->id)
                ->value('status');
        }

        // Truyện cùng tác giả (loại bỏ chính nó)
        $firstAuthor = $article->authors->first();

        $sameAuthorArticles = collect();

        if ($firstAuthor) {
            $sameAuthorArticles = $firstAuthor->articles()
                ->where('articles.id', '!=', $article->id)
                ->latest()
                ->limit(5)
                ->get();
        }

        // Similar: same author and same genres when no manual config is set.
        $genreIds = $article->genres->pluck('id');
        $suggestedArticles = $this->configuredArticles($article->similar_article_ids, $article->id, 10);
        if ($suggestedArticles->isEmpty()) {
            $suggestedArticles = $this->defaultSimilarArticles($article, $firstAuthor, $genreIds, 10);
        }

        // Translation requests: latest articles when no manual config is set.
        $translationRequests = $this->configuredArticles($article->translation_request_article_ids, $article->id, 10);
        if ($translationRequests->isEmpty()) {
            $translationRequests = Article::where('id', '!=', $article->id)
                ->latest()
                ->limit(10)
                ->get();
        }

        // Genres used by the Related Collections block.
        $relatedGenres = $this->configuredGenres($article->related_genre_ids, 6);
        if ($relatedGenres->isEmpty()) {
            $relatedGenres = $article->genres()
                ->withCount('articles')
                ->limit(6)
                ->get();
        }

        return view('client.articles.show', [
            'article' => $article,
            'chapters' => $chapters,
            'upcomingChapters' => $upcomingChapters,
            'chapterPages' => $chapterPages,
            'latestChapters' => $latestChapters,
            'unlockedChapterIds' => $unlockedChapterIds,
            'hasActiveVip' => $hasActiveVip,
            'hasStartedReading' => $hasStartedReading,
            'continueChapterNumber' => $continueChapterNumber,
            'currentListStatus' => $currentListStatus,
            'comments' => $comments,
            'sameAuthorArticles' => $sameAuthorArticles,
            'suggestedArticles' => $suggestedArticles,
            'translationRequests' => $translationRequests,
            'relatedGenres' => $relatedGenres,
        ]);
    }

    /**
     * AJAX: return a range (page) of chapters for the chapter tab dropdown.
     * Matches the contract used by static/book/js/singleee8b.js.
     */
    public function chapterPagination(Request $request)
    {
        $request->validate([
            'book_id' => ['required', 'integer'],
            'page' => ['required', 'integer', 'min:1'],
        ]);

        $article = Article::findOrFail($request->integer('book_id'));
        $page = max(1, (int) $request->integer('page'));

        $chapters = $article->chapters()
            ->orderByDesc('number')
            ->forPage($page, self::CHAPTERS_PER_PAGE)
            ->get();

        $unlockedChapterIds = collect();
        $hasActiveVip = false;

        if (Auth::check()) {
            $hasActiveVip = UserVip::where('user_id', Auth::id())
                ->where('end_at', '>=', now())
                ->exists();

            $unlockedChapterIds = ChapterUnlock::where('user_id', Auth::id())
                ->whereIn('chapter_id', $chapters->pluck('id'))
                ->pluck('chapter_id');
        }

        $html = view('client.articles.partials.chapter-list-items', [
            'chapters' => $chapters,
            'article' => $article,
            'unlockedChapterIds' => $unlockedChapterIds,
            'hasActiveVip' => $hasActiveVip,
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Group ordered chapter numbers into pages and build "min - max" range labels
     * for the chapter-tab pagination dropdown.
     */
    private function buildChapterPages($numbers, int $perPage): array
    {
        $pages = [];

        foreach (collect($numbers)->chunk($perPage)->values() as $index => $chunk) {
            $pages[] = [
                'page' => $index + 1,
                'label' => $chunk->min() . ' - ' . $chunk->max(),
            ];
        }

        return $pages;
    }

    private function configuredArticles($ids, int $articleId, int $limit)
    {
        $ids = collect((array) $ids)
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) use ($articleId) {
                return $id > 0 && $id !== $articleId;
            })
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $items = Article::whereIn('id', $ids->all())->get()->keyBy('id');

        return $ids->map(function ($id) use ($items) {
            return $items->get($id);
        })->filter()->take($limit)->values();
    }

    private function defaultSimilarArticles(Article $article, $author, $genreIds, int $limit)
    {
        $query = Article::where('id', '!=', $article->id);

        if ($author) {
            $query->whereHas('authors', function ($q) use ($author) {
                return $q->where('authors.id', $author->id);
            });
        }

        if ($genreIds->isNotEmpty()) {
            $query->whereHas('genres', function ($q) use ($genreIds) {
                return $q->whereIn('genres.id', $genreIds);
            });
        }

        $items = $query->orderByDesc('view')->latest()->limit($limit)->get();

        if ($items->isNotEmpty()) {
            return $items;
        }

        if ($genreIds->isNotEmpty()) {
            return Article::where('id', '!=', $article->id)
                ->whereHas('genres', function ($q) use ($genreIds) {
                    return $q->whereIn('genres.id', $genreIds);
                })
                ->orderByDesc('view')
                ->latest()
                ->limit($limit)
                ->get();
        }

        if ($author) {
            return Article::where('id', '!=', $article->id)
                ->whereHas('authors', function ($q) use ($author) {
                    return $q->where('authors.id', $author->id);
                })
                ->latest()
                ->limit($limit)
                ->get();
        }

        return Article::where('id', '!=', $article->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function configuredGenres($ids, int $limit)
    {
        $ids = collect((array) $ids)
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $items = Genre::whereIn('id', $ids->all())->withCount('articles')->get()->keyBy('id');

        return $ids->map(function ($id) use ($items) {
            return $items->get($id);
        })->filter()->take($limit)->values();
    }
}
