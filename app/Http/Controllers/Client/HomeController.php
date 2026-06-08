<?php

namespace App\Http\Controllers\Client;

use App\Enums\ArticleCompleteStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $hotArticles         = Article::getHotArticles()->with('genres')->take(16)->get();
        $newUpdateArticles   = Article::getNewUpdateArticles()->with('genres')->take(30)->get();
        $completedArticles   = Article::getCompletedArticles()->take(12)->get();
        $randomArticles      = Article::inRandomOrder()->take(12)->get();   // Translation requests
        $lastComments        = DB::table('comments')
                                 ->join('users', 'users.id', '=', 'comments.user_id')
                                 ->join('articles', 'articles.id', '=', 'comments.article_id')
                                 ->select('comments.*', 'users.name as user_name', 'articles.title as article_title', 'articles.id as article_id')
                                 ->orderByDesc('comments.created_at')
                                 ->limit(6)
                                 ->get();
        // Bookmarks của user hiện tại (không check auth - demo full)
        $myBookmarks = \App\Models\Bookmark::with('article')
                         ->orderByDesc('created_at')
                         ->limit(8)
                         ->get()
                         ->pluck('article')
                         ->filter();

        return view('client.home.index', [
            'hotArticles'       => $hotArticles,
            'newUpdateArticles' => $newUpdateArticles,
            'completedArticles' => $completedArticles,
            'randomArticles'    => $randomArticles,
            'lastComments'      => $lastComments,
            'myBookmarks'       => $myBookmarks,
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
