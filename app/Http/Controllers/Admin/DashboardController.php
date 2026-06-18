<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\ChapterUnlock;
use App\Models\Character;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\ChapterReport;
use App\Models\CommentReport;
use App\Models\Deposit;
use App\Models\Genre;
use App\Models\ReadingHistory;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use App\Models\UserVip;
use App\Scopes\ApprovedArticleScope;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $data = $this->baseDashboardData();

        if (Auth::user()?->is_admin) {
            return view('admin.dashboard.analytics', array_merge($data, $this->analyticsData()));
        }

        return view('admin.dashboard.index', $data);
    }

    private function baseDashboardData(): array
    {
        // Count articles without approval scope so admin totals remain exact.
        $base = fn () => Article::withoutGlobalScope(ApprovedArticleScope::class);

        return [
            'pendingArticles'          => $base()->where('status', ArticleStatus::PENDING->value)->count(),
            'approvedArticles'         => $base()->where('status', ArticleStatus::APPROVED->value)->count(),
            'openReports'              => CommentReport::where('resolved', false)->count(),
            'resolvedReports'          => CommentReport::where('resolved', true)->count(),
            'openChapterReports'       => ChapterReport::where('resolved', false)->count(),
            'resolvedChapterReports'   => ChapterReport::where('resolved', true)->count(),
            'hiddenArticles'           => $base()->where('status', ArticleStatus::HIDDEN->value)->count(),

            'articleCount' => $base()->count(),
            'chapterCount' => Chapter::count(),
            'commentCount' => Comment::count(),
            'userCount'    => User::count(),

            'authorCount'     => Author::count(),
            'genreCount'      => Genre::count(),
            'characterCount'  => Character::count(),
            'teamCount'       => Team::count(),
            'collectionCount' => Collection::count(),
            'tagCount'        => Tag::count(),
            'vipActive'       => UserVip::where('end_at', '>=', now())->distinct('user_id')->count('user_id'),
        ];
    }

    private function analyticsData(): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $trendStart = now()->subDays(29)->startOfDay();
        $trendEnd = now()->endOfDay();

        $completedDepositsThisMonth = Deposit::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$monthStart, $monthEnd]);

        $creditUnlocksThisMonth = ChapterUnlock::query()
            ->whereBetween('created_at', [$monthStart, $monthEnd]);

        $readsThisMonth = ReadingHistory::query()
            ->whereBetween('read_at', [$monthStart, $monthEnd]);

        return [
            'analyticsPeriodLabel' => $monthStart->format('d/m/Y') . ' - ' . $monthEnd->format('d/m/Y'),

            'monthlyBuyers' => (clone $completedDepositsThisMonth)->distinct('user_id')->count('user_id'),
            'monthlyTransactions' => (clone $completedDepositsThisMonth)->count(),
            'monthlyRevenue' => (clone $completedDepositsThisMonth)->sum('amount'),
            'monthlyCreditReaders' => (clone $creditUnlocksThisMonth)->distinct('user_id')->count('user_id'),
            'monthlyCreditUnlocks' => (clone $creditUnlocksThisMonth)->count(),
            'monthlyCreditsSpent' => (clone $creditUnlocksThisMonth)->sum('credits_spent'),
            'monthlyReadingUsers' => (clone $readsThisMonth)->distinct('user_id')->count('user_id'),
            'monthlyReads' => (clone $readsThisMonth)->count(),

            'topPurchasedArticles' => $this->topPurchasedArticles($monthStart, $monthEnd),
            'topViewedArticles' => $this->topViewedArticles($monthStart, $monthEnd),
            'topAllTimeViewedArticles' => Article::withoutGlobalScope(ApprovedArticleScope::class)
                ->orderByDesc('view')
                ->limit(10)
                ->get(['id', 'title', 'cover_image', 'view']),

            'chartLabels' => $this->chartLabels($trendStart, $trendEnd),
            'purchaseChartData' => $this->purchaseChartData($trendStart, $trendEnd),
            'creditChartData' => $this->creditChartData($trendStart, $trendEnd),
            'readChartData' => $this->readChartData($trendStart, $trendEnd),
        ];
    }

    private function topPurchasedArticles($start, $end)
    {
        return Article::withoutGlobalScope(ApprovedArticleScope::class)
            ->join('chapter_unlocks', 'articles.id', '=', 'chapter_unlocks.article_id')
            ->whereBetween('chapter_unlocks.created_at', [$start, $end])
            ->select('articles.id', 'articles.title', 'articles.cover_image', 'articles.view')
            ->selectRaw('COUNT(chapter_unlocks.id) as unlocks_count')
            ->selectRaw('COUNT(DISTINCT chapter_unlocks.user_id) as buyers_count')
            ->selectRaw('COALESCE(SUM(chapter_unlocks.credits_spent), 0) as credits_spent')
            ->groupBy('articles.id', 'articles.title', 'articles.cover_image', 'articles.view')
            ->orderByDesc('unlocks_count')
            ->limit(10)
            ->get();
    }

    private function topViewedArticles($start, $end)
    {
        return Article::withoutGlobalScope(ApprovedArticleScope::class)
            ->join('reading_histories', 'articles.id', '=', 'reading_histories.article_id')
            ->whereBetween('reading_histories.read_at', [$start, $end])
            ->select('articles.id', 'articles.title', 'articles.cover_image', 'articles.view')
            ->selectRaw('COUNT(reading_histories.id) as reads_count')
            ->selectRaw('COUNT(DISTINCT reading_histories.user_id) as readers_count')
            ->groupBy('articles.id', 'articles.title', 'articles.cover_image', 'articles.view')
            ->orderByDesc('reads_count')
            ->limit(10)
            ->get();
    }

    private function chartLabels($start, $end): array
    {
        return collect(CarbonPeriod::create($start, $end))
            ->map(fn ($day) => $day->format('d/m'))
            ->values()
            ->all();
    }

    private function purchaseChartData($start, $end): array
    {
        $rows = Deposit::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw('COUNT(DISTINCT user_id) as buyers')
            ->selectRaw('COALESCE(SUM(amount), 0) as revenue')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        return $this->fillDailySeries($start, $end, $rows, [
            'transactions' => 'transactions',
            'buyers' => 'buyers',
            'revenue' => 'revenue',
        ]);
    }

    private function creditChartData($start, $end): array
    {
        $rows = ChapterUnlock::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('COUNT(*) as unlocks')
            ->selectRaw('COUNT(DISTINCT user_id) as readers')
            ->selectRaw('COALESCE(SUM(credits_spent), 0) as credits')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        return $this->fillDailySeries($start, $end, $rows, [
            'unlocks' => 'unlocks',
            'readers' => 'readers',
            'credits' => 'credits',
        ]);
    }

    private function readChartData($start, $end): array
    {
        $rows = ReadingHistory::query()
            ->whereBetween('read_at', [$start, $end])
            ->selectRaw('DATE(read_at) as day')
            ->selectRaw('COUNT(*) as read_events')
            ->selectRaw('COUNT(DISTINCT user_id) as readers')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        return $this->fillDailySeries($start, $end, $rows, [
            'read_events' => 'read_events',
            'readers' => 'readers',
        ]);
    }

    private function fillDailySeries($start, $end, $rows, array $fields): array
    {
        $series = [];
        foreach ($fields as $name => $field) {
            $series[$name] = [];
        }

        foreach (CarbonPeriod::create($start, $end) as $day) {
            $key = $day->toDateString();
            $row = $rows->get($key);

            foreach ($fields as $name => $field) {
                $series[$name][] = $row ? (float) $row->{$field} : 0;
            }
        }

        return $series;
    }
}
