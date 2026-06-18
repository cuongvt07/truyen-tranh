<?php

namespace App\Providers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ChapterReport;
use App\Models\CommentReport;
use App\Scopes\ApprovedArticleScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AdminLayoutServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            $currentUser = Auth::user();
            $view->with('currentUser', $currentUser);
            $view->with('isAdminUser', (bool) $currentUser);

            if ($currentUser) {
                $pendingArticles    = Article::withoutGlobalScope(ApprovedArticleScope::class)
                    ->where('status', ArticleStatus::PENDING->value)->count();
                $openCommentReports = CommentReport::where('resolved', false)->count();
                $openChapterReports = ChapterReport::where('resolved', false)->count();

                $view->with([
                    'navPendingArticles'    => $pendingArticles,
                    'navOpenReports'        => $openCommentReports,
                    'navOpenChapterReports' => $openChapterReports,
                    'navPendingTotal'       => $pendingArticles + $openCommentReports + $openChapterReports,
                ]);
            }
        });
    }
}
