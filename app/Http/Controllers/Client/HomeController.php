<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Genre;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $data = Cache::remember('home:index:alphanovel:v1', 180, function () {
            $take = 10;
            $with = ['genres', 'authors', 'slug'];

            $articleList = function ($query) use ($take, $with) {
                return $query
                    ->with($with)
                    ->withCount('chapters')
                    ->take($take)
                    ->get();
            };

            $hotArticles = $articleList(Article::getHotArticles());
            $newUpdateArticles = $articleList(Article::getNewUpdateArticles());
            $hottestNewArticles = $articleList(Article::query()->latest('created_at'));
            $exclusiveArticles = $articleList(Article::where('is_user_submitted', true)->latest('id'));

            if ($exclusiveArticles->isEmpty()) {
                $exclusiveArticles = $newUpdateArticles;
            }

            $genreBlock = function (array $names) use ($articleList, $hotArticles) {
                $genre = Genre::query()
                    ->whereIn('name', $names)
                    ->orderBy('name')
                    ->first();

                if (!$genre) {
                    return [
                        'articles' => $hotArticles,
                        'url' => route('catalog.index'),
                    ];
                }

                $articles = $articleList(
                    Article::query()
                        ->whereHas('genres', fn ($q) => $q->where('genres.id', $genre->id))
                        ->orderByDesc('view')
                );

                return [
                    'articles' => $articles->isNotEmpty() ? $articles : $hotArticles,
                    'url' => route('catalog.index', ['genre' => $genre->getRouteKey()]),
                ];
            };

            $blocks = [
                [
                    'title' => 'Best match for you',
                    'articles' => $hotArticles,
                    'url' => route('home.show_hot_articles'),
                    'variant' => 'rail',
                    'showSeeAll' => false,
                ],
                [
                    'title' => 'Top Trending',
                    'articles' => $newUpdateArticles,
                    'url' => route('home.show_new_update_articles'),
                    'variant' => 'trending',
                    'showSeeAll' => true,
                ],
                [
                    'title' => 'Hottest New',
                    'articles' => $hottestNewArticles,
                    'url' => route('home.show_new_update_articles'),
                    'variant' => 'rail',
                    'showSeeAll' => true,
                ],
                [
                    'title' => "Editors' Choice",
                    'articles' => $hotArticles,
                    'url' => route('home.show_hot_articles'),
                    'variant' => 'rail',
                    'showSeeAll' => true,
                ],
                [
                    'title' => 'Only at '.config('app.name'),
                    'articles' => $exclusiveArticles,
                    'url' => route('catalog.index'),
                    'variant' => 'rail',
                    'showSeeAll' => true,
                ],
            ];

            foreach ([
                'Top Werewolf' => ['Werewolf'],
                'Top Billionaire/CEO' => ['Billionaire/CEO', 'Billionaire', 'CEO'],
                'Top Romance' => ['Romance', 'Tinh cam', 'Tình cảm'],
                'Top Paranormal' => ['Paranormal', 'Supernatural'],
                'Top Fantasy' => ['Fantasy', 'Tham hiem', 'Thám hiểm', 'Xuyen khong', 'Xuyên không'],
                'Top YA/Teen' => ['YA/Teen', 'Young Adult', 'School Life'],
                'Top LGBTQ+' => ['LGBTQ+', 'LGBTQ'],
            ] as $title => $names) {
                $block = $genreBlock($names);
                $blocks[] = [
                    'title' => $title,
                    'articles' => $block['articles'],
                    'url' => $block['url'],
                    'variant' => 'rail',
                    'showSeeAll' => true,
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
            ->whereHas('chapters', fn ($q) => $q->where('created_at', '>=', now()->subDays(3)))
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
        $topTags = Tag::query()
            ->withCount('articles')
            ->orderByDesc('articles_count')
            ->orderBy('name')
            ->take(18)
            ->get();

        if ($topTags->isEmpty()) {
            $topTags = Genre::query()
                ->withCount('articles')
                ->orderByDesc('articles_count')
                ->orderBy('name')
                ->take(18)
                ->get();
        }

        $articles = Article::query()
            ->with(['authors', 'genres', 'tags', 'slug'])
            ->withCount('chapters')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%'.$keyword.'%')
                        ->orWhere('description', 'like', '%'.$keyword.'%')
                        ->orWhereHas('authors', fn ($author) => $author->where('name', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('genres', fn ($genre) => $genre->where('name', 'like', '%'.$keyword.'%'))
                        ->orWhereHas('tags', fn ($tag) => $tag->where('name', 'like', '%'.$keyword.'%'));
                });
            }, function ($query) {
                $query->orderByDesc('view');
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('client.home.search', [
            'articles' => $articles,
            'keyword' => $keyword,
            'topTags' => $topTags,
            'title' => $keyword !== '' ? 'Search results for "'.$keyword.'"' : 'Search',
            'description' => 'You can search for any novel name, author name, or novel tag you want to search.',
        ]);
    }
}
