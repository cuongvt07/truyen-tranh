<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Character;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use App\Models\UserVip;
use App\Scopes\ApprovedArticleScope;

class DashboardController extends Controller
{
    public function index()
    {
        // Đếm truyện không qua scope duyệt để có số tổng chính xác
        $base = fn () => Article::withoutGlobalScope(ApprovedArticleScope::class);

        return view('admin.dashboard.index', [
            // Cần xử lý (chờ)
            'pendingArticles' => $base()->where('status', ArticleStatus::PENDING->value)->count(),
            'openReports'     => CommentReport::where('resolved', false)->count(),
            'hiddenArticles'  => $base()->where('status', ArticleStatus::HIDDEN->value)->count(),

            // Tổng quan
            'articleCount' => $base()->count(),
            'chapterCount' => Chapter::count(),
            'commentCount' => Comment::count(),
            'userCount'    => User::count(),

            // Thống kê khác
            'authorCount'     => Author::count(),
            'genreCount'      => Genre::count(),
            'characterCount'  => Character::count(),
            'teamCount'       => Team::count(),
            'collectionCount' => Collection::count(),
            'tagCount'        => Tag::count(),
            'vipActive'       => UserVip::where('end_at', '>=', now())->distinct('user_id')->count('user_id'),
        ]);
    }
}
