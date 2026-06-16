<?php

namespace App\Http\Controllers\Client;

use App\Enums\ArticleCompleteStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Collection;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $hotArticles         = Article::getHotArticles()->with('genres')->take(16)->get();
        $newUpdateArticles   = Article::getNewUpdateArticles()->with('genres')
            ->withMax('chapters', 'created_at') // ngày chương mới nhất (đã đăng) -> chapters_max_created_at
            ->take(30)->get();
        $completedArticles   = Article::getCompletedArticles()->take(12)->get();
        // "Translation requests": truyện do USER tự gửi (/dang-truyen) đã được admin DUYỆT
        // (ApprovedArticleScope tự lọc status=APPROVED), mới nhất.
        $userSubmittedArticles = Article::where('is_user_submitted', true)
            ->latest('id')->take(12)->get();
        $lastComments        = DB::table('comments')
                                 ->join('users', 'users.id', '=', 'comments.user_id')
                                 ->join('articles', 'articles.id', '=', 'comments.article_id')
                                 ->leftJoin('slugs', function ($join) {
                                     $join->on('slugs.sluggable_id', '=', 'articles.id')
                                         ->where('slugs.sluggable_type', Article::class)
                                         ->where('slugs.type', 'article');
                                 })
                                 ->select('comments.*', 'users.name as user_name', 'articles.title as article_title', 'articles.id as article_id', 'slugs.slug as article_slug')
                                 ->orderByDesc('comments.created_at')
                                 ->limit(6)
                                 ->get();
        $readingHistory = Auth::check()
            ? ReadingHistory::continueReading(Auth::id(), 6)
            : collect();
        $lastCollections = Collection::query()
            ->with(['user:id,name,username', 'articles.slug'])
            ->withCount('articles')
            ->where('is_private', false)
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        $collectionIds = $lastCollections->pluck('id');
        $collectionCommentCounts = $collectionIds->isNotEmpty()
            ? DB::table('collection_article')
                ->join('comments', 'comments.article_id', '=', 'collection_article.article_id')
                ->whereIn('collection_article.collection_id', $collectionIds)
                ->selectRaw('collection_article.collection_id, COUNT(comments.id) as total')
                ->groupBy('collection_article.collection_id')
                ->pluck('total', 'collection_id')
            : collect();

        $lastCollections->each(function ($collection) use ($collectionCommentCounts) {
            $collection->setAttribute('comments_count', (int) ($collectionCommentCounts[$collection->id] ?? 0));
        });

        return view('client.home.index', [
            'hotArticles'       => $hotArticles,
            'newUpdateArticles' => $newUpdateArticles,
            'completedArticles' => $completedArticles,
            'userSubmittedArticles' => $userSubmittedArticles,
            'lastComments'      => $lastComments,
            'readingHistory'    => $readingHistory,
            'lastCollections'   => $lastCollections,
        ]);
    }

    public function showHotArticles()
    {
        $hotArticles = Article::getHotArticles()->paginate();
        return view('client.articles.index', [
            'articles' => $hotArticles,
            'title' => 'Truyện đọc nhiều nhất',
            'description' => 'Danh sách những truyện đang hot, có nhiều người đọc và quan tâm nhất trong tháng này',
        ]);
    }

    public function showNewUpdateArticles()
    {
        $newUpdateArticles = Article::getNewUpdateArticles()->paginate();
        return view('client.articles.index', [
            'articles' => $newUpdateArticles,
            'title' => 'Truyện mới cập nhật',
            'description' => 'Danh sách truyện chữ được cập nhật (vừa ra mắt, thêm chương mới, sửa nội dung,..) gần đây.',
        ]);
    }

    public function showCompletedArticles()
    {
        $completedArticles = Article::getCompletedArticles()->paginate();
        return view('client.articles.index', [
            'articles' => $completedArticles,
            'title' => 'Truyện đã hoàn thành',
            'description' => 'Danh sách những truyện đã hoàn thành, ra đủ chương.',
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $articles = Article::query()->where('title', 'like', '%' . $keyword . '%')->paginate();
        return view('client.articles.index', [
            'articles' => $articles,
            'title' => 'Tìm kiếm cho từ khoá "' . $keyword . '"',
            'description' => 'Danh sách truyện có liên quan tới từ khoá "' . $keyword . '"',
        ]);
    }
}
