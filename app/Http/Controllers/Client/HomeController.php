<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Genre;
use App\Models\HomeBlock;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $data = Cache::remember(HomeBlock::CACHE_KEY, 180, function () {
            $take = 9;
            $trendingTake = 8;
            $with = ['genres', 'authors', 'slug'];

            $articleList = function ($query, ?int $limit = null) use ($take, $with) {
                return $query
                    ->with($with)
                    ->withCount('chapters')
                    ->take($limit ?? $take)
                    ->get();
            };

            $topUpArticles = function ($articles, ...$args) use ($take) {
                $limit = $take;
                if ($args && is_int(end($args))) {
                    $limit = array_pop($args);
                }

                $articles = collect($articles)->take($limit)->values();
                $fallbacks = $args;

                if ($articles->count() >= $limit) {
                    return $articles;
                }

                $seen = $articles->pluck('id')->filter()->all();

                foreach ($fallbacks as $fallback) {
                    foreach (collect($fallback) as $article) {
                        if (!$article || in_array($article->id, $seen, true)) {
                            continue;
                        }

                        $articles->push($article);
                        $seen[] = $article->id;

                        if ($articles->count() >= $limit) {
                            return $articles->values();
                        }
                    }
                }

                return $articles->values();
            };

            $hotArticles = $articleList(Article::getHotArticles());
            $newUpdateArticles = $articleList(Article::getNewUpdateArticles());
            $topTrendingArticles = $topUpArticles(
                $articleList(Article::getNewUpdateArticles(), $trendingTake),
                $newUpdateArticles,
                $hotArticles,
                $trendingTake
            );
            $hottestNewArticles = $articleList(Article::query()->latest('created_at'));
            $exclusiveArticles = $articleList(Article::where('is_user_submitted', true)->latest('id'));

            $exclusiveArticles = $topUpArticles($exclusiveArticles, $newUpdateArticles, $hotArticles);

            // Mỗi khối là một truy vấn riêng -> chỉ dựng đúng các khối đang bật,
            // theo thứ tự admin đặt. Nạp sẵn genre để không N+1 khi suy ra link.
            $configuredBlocks = HomeBlock::with('genre')
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            $queryForSource = function (HomeBlock $block) use ($hotArticles, $newUpdateArticles, $topTrendingArticles) {
                switch ($block->source) {
                    case 'trending':
                        return ['collection' => $topTrendingArticles, 'url' => route_path('home.show_new_update_articles')];
                    case 'hot':
                        return ['collection' => $hotArticles, 'url' => route_path('home.show_hot_articles')];
                    case 'new_update':
                        return ['collection' => $newUpdateArticles, 'url' => route_path('home.show_new_update_articles')];
                    case 'latest':
                        return ['query' => Article::query()->latest('created_at'), 'url' => route_path('home.show_new_update_articles')];
                    case 'exclusive':
                        return ['query' => Article::where('is_user_submitted', true)->latest('id'), 'url' => route_path('catalog.index')];
                    case 'genre':
                    default:
                        $genre = $block->genre;
                        if (!$genre) {
                            return ['collection' => collect(), 'url' => route_path('catalog.index')];
                        }

                        return [
                            'query' => Article::query()
                                ->whereHas('genres', fn ($q) => $q->where('genres.id', $genre->id))
                                ->orderByDesc('view'),
                            'url' => route_path('catalog.index', ['genre' => $genre->getRouteKey()]),
                        ];
                }
            };

            $blocks = [];

            foreach ($configuredBlocks as $configured) {
                $resolved = $queryForSource($configured);
                $limit = max(1, (int) $configured->limit);

                // 'collection' = dùng lại dữ liệu đã nạp ở trên (không thêm truy vấn);
                // 'query' = khối này cần truy vấn riêng.
                $articles = array_key_exists('collection', $resolved)
                    ? collect($resolved['collection'])
                    : $articleList($resolved['query'], $limit);

                // Khối thiếu truyện thì bù từ hot/mới cập nhật để không bị trống.
                $articles = $topUpArticles($articles, $hotArticles, $newUpdateArticles, $limit);

                if ($articles->isEmpty()) {
                    continue;
                }

                $blocks[] = [
                    'title' => $configured->title,
                    'articles' => $articles,
                    'url' => $configured->url ?: $resolved['url'],
                    'variant' => $configured->variant,
                    'showSeeAll' => (bool) $configured->show_see_all,
                ];
            }

            return [
                'hotArticles' => $hotArticles,
                'newUpdateArticles' => $newUpdateArticles,
                'discoverBlocks' => $blocks,
            ];
        });

        return view('client.home.index', $data);
    }

    public function showHotArticles()
    {
        $hotArticles = Article::getHotArticles()->with(['authors', 'slug'])->paginate();

        return view('client.articles.index', [
            'articles' => $hotArticles,
            'title' => 'Most Read Stories',
            'description' => 'The most-read stories currently getting the strongest reader attention.',
        ]);
    }

    public function showNewUpdateArticles()
    {
        $newUpdateArticles = Article::getNewUpdateArticles()
            ->with(['authors', 'slug'])
            ->whereHas('chapters', function ($q) {
                $q->where('created_at', '>=', now()->subDays(3));
            })
            ->paginate();

        return view('client.articles.index', [
            'articles' => $newUpdateArticles,
            'title' => 'Recently Updated',
            'description' => 'Stories with new chapters released in the last 3 days.',
        ]);
    }

    public function showCompletedArticles()
    {
        $completedArticles = Article::getCompletedArticles()->with(['authors', 'slug'])->paginate();

        return view('client.articles.index', [
            'articles' => $completedArticles,
            'title' => 'Completed Stories',
            'description' => 'Stories that are fully completed and ready to read from beginning to end.',
        ]);
    }

    public function search(Request $request)
    {
        $keyword = trim((string) ($request->input('keyword') ?: $request->input('q')));
        $genreId = (int) $request->input('genre');
        $activeGenre = $genreId ? Genre::find($genreId) : null;

        // Hàng thể loại để lọc; chỉ lấy thể loại thực sự có truyện.
        $filterGenres = Genre::query()
            ->withCount('articles')
            ->having('articles_count', '>', 0)
            ->orderByDesc('articles_count')
            ->orderBy('name')
            ->take(24)
            ->get();

        // Top Tags bám theo thể loại đang chọn: chỉ đếm tag của truyện thuộc
        // thể loại đó, thay vì xếp hạng trên toàn site.
        $topTags = Tag::query()
            ->withCount(['articles' => function ($q) use ($activeGenre) {
                if ($activeGenre) {
                    $q->whereHas('genres', fn ($g) => $g->where('genres.id', $activeGenre->id));
                }
            }])
            ->when($activeGenre, function ($q) use ($activeGenre) {
                $q->whereHas('articles', fn ($a) => $a->whereHas(
                    'genres', fn ($g) => $g->where('genres.id', $activeGenre->id)
                ));
            })
            ->having('articles_count', '>', 0)
            ->orderByDesc('articles_count')
            ->orderBy('name')
            ->take(18)
            ->get();

        if ($topTags->isEmpty()) {
            $topTags = $filterGenres->take(18);
        }

        $articles = Article::query()
            ->with([
                'authors',
                'genres',
                'tags',
                'slug',
                'bookmarks' => fn ($query) => $query->where('user_id', auth()->id()),
            ])
            ->withCount('chapters')
            ->when($activeGenre, function ($query) use ($activeGenre) {
                $query->whereHas('genres', fn ($g) => $g->where('genres.id', $activeGenre->id));
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%'.$keyword.'%')
                        ->orWhere('description', 'like', '%'.$keyword.'%')
                        ->orWhereHas('authors', function ($author) use ($keyword) {
                            $author->where('name', 'like', '%'.$keyword.'%');
                        })
                        ->orWhereHas('genres', function ($genre) use ($keyword) {
                            $genre->where('name', 'like', '%'.$keyword.'%');
                        })
                        ->orWhereHas('tags', function ($tag) use ($keyword) {
                            $tag->where('name', 'like', '%'.$keyword.'%');
                        });
                });
            }, function ($query) {
                $query->orderByDesc('view');
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $formatCompact = function ($value) {
            $value = (int) $value;
            if ($value >= 1000000) return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.').'M';
            if ($value >= 1000) return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'K';
            return number_format($value);
        };

        // Gõ hoặc đổi thể loại chỉ nạp lại đúng hai khối thay đổi (tag + kết quả),
        // không dựng lại cả trang.
        if ($request->ajax()) {
            return response()->json([
                'tags' => view('client.home.partials.search-tags', [
                    'topTags' => $topTags,
                    'activeGenre' => $activeGenre,
                    'keyword' => $keyword,
                ])->render(),
                'results' => view('client.home.partials.search-results', [
                    'articles' => $articles,
                    'keyword' => $keyword,
                    'formatCompact' => $formatCompact,
                ])->render(),
            ]);
        }

        return view('client.home.search', [
            'articles' => $articles,
            'keyword' => $keyword,
            'topTags' => $topTags,
            'filterGenres' => $filterGenres,
            'activeGenre' => $activeGenre,
            'title' => $keyword !== '' ? 'Search results for "'.$keyword.'"' : 'Search',
            'description' => 'You can search for any novel name, author name, or novel tag you want to search.',
        ]);
    }
}
