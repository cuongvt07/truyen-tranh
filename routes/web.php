<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Client\BookmarkController;
use App\Http\Controllers\Client\BuyPackageVipController;
use App\Http\Controllers\Client\CommentController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SePayOverrideController;
use App\Http\Controllers\UserController as UserAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Route auth
Route::middleware(['auth'])->group(function () {
    // users
    Route::get('/users/change-password',
        [UserAuthController::class, 'changePassword'])
        ->name('users.change_password');
    Route::get('/users/change-info',
        [UserAuthController::class, 'changeInfo'])
        ->name('users.change_info');
    Route::patch('/users/change-info',
        [UserAuthController::class, 'update'])
        ->name('users.update');
    Route::get('/users',
        [UserAuthController::class, 'show'])
        ->name('users.show');
    Route::get('/banned',
        [UserAuthController::class, 'handleBanned'])
        ->name('users.handle_banned');
    Route::post('/vip/buy',
        [BuyPackageVipController::class, 'buyVip'])->name('vip.buy');
    Route::post('/generate-qr', [PaymentController::class, 'createDeposit'])->name('generate.qr');
    Route::post('/transactions/check', [PaymentController::class, 'checkTransactionStatus'])
        ->name('sepay.transactions.check');

    // Đăng & quản lý truyện của user (scoped own, giao diện novelight)
    Route::get('/upload', [\App\Http\Controllers\Client\MyArticleController::class, 'create'])->name('my-articles.create');
    Route::post('/upload', [\App\Http\Controllers\Client\MyArticleController::class, 'store'])->name('my-articles.store');
    Route::get('/my-books', [\App\Http\Controllers\Client\MyArticleController::class, 'index'])->name('my-articles.index');
    Route::get('/my-books/{article}/edit', [\App\Http\Controllers\Client\MyArticleController::class, 'edit'])->name('my-articles.edit');
    Route::patch('/my-books/{article}', [\App\Http\Controllers\Client\MyArticleController::class, 'update'])->name('my-articles.update');
    Route::delete('/my-books/{article}', [\App\Http\Controllers\Client\MyArticleController::class, 'destroy'])->name('my-articles.destroy');
    Route::get('/my-books/{article}/add-chapter', [\App\Http\Controllers\Client\MyArticleController::class, 'createChapter'])->name('my-articles.create_chapter');
    Route::post('/my-books/{article}/add-chapter', [\App\Http\Controllers\Client\MyArticleController::class, 'storeChapter'])->name('my-articles.store_chapter');

    // Nhóm dịch / Bộ sưu tập (community, scoped owner). Nhân vật quản lý ở admin (toàn site).
    Route::get('/teams/create', [\App\Http\Controllers\Client\TeamController::class, 'create'])->name('teams.create');
    Route::get('/teams/{team}', [\App\Http\Controllers\Client\TeamController::class, 'show'])->name('teams.show');
    Route::get('/teams/{team}/dashboard', [\App\Http\Controllers\Client\TeamController::class, 'dashboard'])->name('teams.dashboard');
    Route::get('/teams/{id}/members/manage', [\App\Http\Controllers\Client\TeamController::class, 'manageMembers'])->name('teams.members.manage');
    Route::post('/teams/{team}/join', [\App\Http\Controllers\Client\TeamController::class, 'joinRequest'])->name('teams.join');
    Route::post('/teams/{id}/members', [\App\Http\Controllers\Client\TeamController::class, 'requestMember'])->name('teams.members.request');
    Route::patch('/teams/{id}/members/{member}', [\App\Http\Controllers\Client\TeamController::class, 'updateMember'])->name('teams.members.update');
    Route::delete('/teams/{id}/members/{member}', [\App\Http\Controllers\Client\TeamController::class, 'removeMember'])->name('teams.members.remove');
    Route::resource('teams', \App\Http\Controllers\Client\TeamController::class)->except(['create', 'show'])->parameters(['teams' => 'id']);
    Route::get('/collections/create', [\App\Http\Controllers\Client\CollectionController::class, 'create'])->name('collections.create');
    Route::resource('collections', \App\Http\Controllers\Client\CollectionController::class)->except(['create', 'show'])->parameters(['collections' => 'id']);
    // articles
    //      articles - comments
    // throttle: chống spam comment / vote / report
    Route::post('/articles/{article}/comments',
        [CommentController::class, 'store'])
        ->middleware('throttle:15,1')
        ->name('articles.comments.store');
    Route::delete('/articles/{article}/comments/{comment}',
        [CommentController::class, 'destroy'])
        ->name('articles.comments.destroy');
    Route::post('/comments/{comment}/vote',
        [CommentController::class, 'vote'])
        ->middleware('throttle:40,1')
        ->name('comments.vote');
    Route::post('/comments/{comment}/report',
        [CommentController::class, 'report'])
        ->middleware('throttle:10,1')
        ->name('comments.report');
    //      articles - bookmarks
    Route::post('/articles/{article}/bookmarks',
        [BookmarkController::class, 'store'])
        ->name('articles.bookmarks.store');
    Route::delete('/articles/{article}/bookmarks/{bookmark}',
        [BookmarkController::class, 'destroy'])
        ->name('articles.bookmarks.destroy');
    // Route admin, authorize: poster, admin
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(
            ['check_role:'.UserRole::POSTER->value.','.UserRole::ADMIN->value]
        )
        ->group(function () {
            // dashboard: QTV thấy analytics, các role còn lại thấy tổng quan hiện tại
            Route::get('/',
                [DashboardController::class, 'index'])
                ->name('dashboard');

            // authorize: admin
            Route::group(
                ['middleware' => ['check_role:'.UserRole::ADMIN->value]],
                function () {
                    // logout
                    Route::delete('/logout',
                        [DashboardController::class, 'index'])
                        ->name('logout');
                    // authors
                    Route::resource('authors', AuthorController::class);
                    // genres
                    Route::resource('genres', GenreController::class);
                    // countries
                    Route::resource('countries', \App\Http\Controllers\Admin\CountryController::class)->except('show', 'create', 'edit');
                    // achievements
                    Route::get('achievements', [\App\Http\Controllers\Admin\AchievementController::class, 'index'])->name('achievements.index');
                    Route::post('achievements', [\App\Http\Controllers\Admin\AchievementController::class, 'store'])->name('achievements.store');
                    Route::put('achievements/{achievement}', [\App\Http\Controllers\Admin\AchievementController::class, 'update'])->name('achievements.update');
                    Route::delete('achievements/{achievement}', [\App\Http\Controllers\Admin\AchievementController::class, 'destroy'])->name('achievements.destroy');
                    Route::get('achievements/{achievement}/users', [\App\Http\Controllers\Admin\AchievementController::class, 'users'])->name('achievements.users');
                    // menus (WordPress-style: kéo-thả 1 trang, cha-con dropdown)
                    Route::get('menus', [MenuController::class, 'index'])->name('menus.index');
                    Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
                    Route::post('menus/{menu}/add-from-source', [MenuController::class, 'addFromSource'])->name('menus.items.addFromSource');
                    Route::put('menu-items/{item}', [MenuController::class, 'updateItem'])->name('menus.items.update');
                    Route::delete('menu-items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');
                    Route::post('menus/{menu}/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');
                    // settings
                    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
                    Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
                    Route::resource('static-pages', \App\Http\Controllers\Admin\StaticPageController::class)->except('show');
                    // ads (quảng cáo)
                    Route::post('ads/settings', [\App\Http\Controllers\Admin\AdController::class, 'updateSettings'])->name('ads.settings');
                    Route::post('ads/{ad}/toggle', [\App\Http\Controllers\Admin\AdController::class, 'toggle'])->name('ads.toggle');
                    Route::resource('ads', \App\Http\Controllers\Admin\AdController::class)->except('show');
                    // SEO settings
                    Route::get('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'settings'])->name('seo.settings');
                    Route::post('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'updateSettings'])->name('seo.update');
                    // users
                    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
                    Route::get('/users/admin',
                        [UserController::class, 'showAdmins'])
                        ->name('users.admin');
                    Route::get('/users/poster',
                        [UserController::class, 'showPosters'])
                        ->name('users.poster');
                    Route::get('/users/banned',
                        [UserController::class, 'showBanneds'])
                        ->name('users.banned');
                    Route::get('/users/{user}/create-ban',
                        [UserController::class, 'createBan'])
                        ->name('users.create_ban');
                    Route::post('/users/{user}/create-ban',
                        [UserController::class, 'storeBan'])
                        ->name('users.store_ban');
                    Route::get('/users/{user}/edit-ban',
                        [UserController::class, 'editBan'])
                        ->name('users.edit_ban');
                    Route::patch('/users/{user}/edit-ban',
                        [UserController::class, 'updateBan'])
                        ->name('users.update_ban');
                    Route::delete('/users/{user}/unban',
                        [UserController::class, 'unban'])
                        ->name('users.unban');
                    Route::get('/users/{user}/edit-role',
                        [UserController::class, 'editRole'])
                        ->name('users.edit_role');
                    Route::patch('/users/{user}/update-role',
                        [UserController::class, 'updateRole'])
                        ->name('users.update_role');
                    Route::resource('users', UserController::class);
                });
            // articles
            Route::get('/articles/{article}/show-chapters',
                [ChapterController::class, 'index'])
                ->name('articles.show_chapters');
            Route::get('/articles/{article}/create-chapter',
                [ChapterController::class, 'create'])
                ->name('articles.create_chapter');
            Route::post('/articles/{article}/store-chapter',
                [ChapterController::class, 'store'])
                ->name('articles.store_chapter');
            Route::get('/articles/{article}/edit-chapter/{chapter}',
                [ChapterController::class, 'edit'])
                ->name('articles.edit_chapter');
            Route::patch('/articles/{article}/update-chapter/{chapter}',
                [ChapterController::class, 'update'])
                ->name('articles.update_chapter');
            Route::delete('/articles/{article}/destroy-chapter/{chapter}',
                [ChapterController::class, 'destroy'])
                ->name('articles.destroy_chapter');
            Route::patch('/articles/{article}/change-status/{status}',
                [ArticleController::class, 'updateStatus'])
                ->name('articles.change_status');
            Route::patch('/articles/{article}/change-complete-status',
                [ArticleController::class, 'updateCompleteStatus'])
                ->name('articles.change_complete_status');
            Route::resource('articles', ArticleController::class);

            // Module cộng đồng (admin/poster quản lý toàn bộ)
            Route::resource('characters', \App\Http\Controllers\Admin\CharacterController::class)->except('show');
            Route::get('teams/pending', [\App\Http\Controllers\Admin\TeamController::class, 'pendingRequests'])->name('teams.pending');
            Route::post('teams/{team}/approve', [\App\Http\Controllers\Admin\TeamController::class, 'approveTeam'])->name('teams.approve');
            Route::post('teams/{team}/reject', [\App\Http\Controllers\Admin\TeamController::class, 'rejectTeam'])->name('teams.reject');
            Route::get('teams/{team}/members', [\App\Http\Controllers\Admin\TeamController::class, 'members'])->name('teams.members');
            Route::post('teams/{team}/members', [\App\Http\Controllers\Admin\TeamController::class, 'addMember'])->name('teams.members.add');
            Route::post('teams/{team}/members/{member}/approve', [\App\Http\Controllers\Admin\TeamController::class, 'approveMember'])->name('teams.members.approve');
            Route::post('teams/{team}/members/{member}/reject', [\App\Http\Controllers\Admin\TeamController::class, 'rejectMember'])->name('teams.members.reject');
            Route::delete('teams/{team}/members/{member}', [\App\Http\Controllers\Admin\TeamController::class, 'removeMember'])->name('teams.members.remove');
            Route::resource('teams', \App\Http\Controllers\Admin\TeamController::class)->except('show');
            Route::resource('collections', \App\Http\Controllers\Admin\CollectionController::class)->except('show');

            // Tags (quản lý + gộp)
            Route::get('tags', [\App\Http\Controllers\Admin\TagController::class, 'index'])->name('tags.index');
            Route::post('tags', [\App\Http\Controllers\Admin\TagController::class, 'store'])->name('tags.store');
            Route::patch('tags/{tag}', [\App\Http\Controllers\Admin\TagController::class, 'update'])->name('tags.update');
            Route::delete('tags/{tag}', [\App\Http\Controllers\Admin\TagController::class, 'destroy'])->name('tags.destroy');
            Route::post('tags/merge', [\App\Http\Controllers\Admin\TagController::class, 'merge'])->name('tags.merge');

            // Chương toàn cục
            Route::get('chapters', [ChapterController::class, 'allIndex'])->name('chapters.all');

            // Quản lý bình luận (moderation)
            Route::get('comments', [\App\Http\Controllers\Admin\CommentController::class, 'index'])->name('comments.index');
            Route::patch('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'update'])->name('comments.update');
            Route::post('comments/{comment}/reply', [\App\Http\Controllers\Admin\CommentController::class, 'reply'])->name('comments.reply');
            Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comments.destroy');
            Route::post('comments/bulk-destroy', [\App\Http\Controllers\Admin\CommentController::class, 'bulkDestroy'])->name('comments.bulk_destroy');
            Route::patch('comments/{comment}/toggle-hidden', [\App\Http\Controllers\Admin\CommentController::class, 'toggleHidden'])->name('comments.toggle_hidden');
            // Báo cáo bình luận
            Route::get('comment-reports', [\App\Http\Controllers\Admin\CommentController::class, 'reports'])->name('comment_reports.index');
            Route::post('comment-reports/{comment}/resolve', [\App\Http\Controllers\Admin\CommentController::class, 'resolveReports'])->name('comment_reports.resolve');
            // Static page comments
            Route::get('static-page-comments', [\App\Http\Controllers\Admin\StaticPageCommentController::class, 'index'])->name('static_page_comments.index');
            Route::patch('static-page-comments/{comment}', [\App\Http\Controllers\Admin\StaticPageCommentController::class, 'update'])->name('static_page_comments.update');
            Route::post('static-page-comments/{comment}/reply', [\App\Http\Controllers\Admin\StaticPageCommentController::class, 'reply'])->name('static_page_comments.reply');
            Route::delete('static-page-comments/{comment}', [\App\Http\Controllers\Admin\StaticPageCommentController::class, 'destroy'])->name('static_page_comments.destroy');
            Route::post('static-page-comments/bulk-destroy', [\App\Http\Controllers\Admin\StaticPageCommentController::class, 'bulkDestroy'])->name('static_page_comments.bulk_destroy');
            Route::patch('static-page-comments/{comment}/toggle-hidden', [\App\Http\Controllers\Admin\StaticPageCommentController::class, 'toggleHidden'])->name('static_page_comments.toggle_hidden');
            // Báo cáo lỗi chương
            Route::get('chapter-reports', [\App\Http\Controllers\Admin\ChapterReportController::class, 'index'])->name('chapter_reports.index');
            Route::post('chapter-reports/resolve-chapter', [\App\Http\Controllers\Admin\ChapterReportController::class, 'resolveChapter'])->name('chapter_reports.resolve_chapter');
            Route::post('chapter-reports/{report}/resolve', [\App\Http\Controllers\Admin\ChapterReportController::class, 'resolve'])->name('chapter_reports.resolve');
            Route::delete('chapter-reports/{report}', [\App\Http\Controllers\Admin\ChapterReportController::class, 'destroy'])->name('chapter_reports.destroy');

            // ===== FORUM MODULE =====
            Route::prefix('forum')->name('forum.')->group(function () {
                // Categories
                Route::resource('categories', \App\Http\Controllers\Admin\Forum\CategoryController::class)->except('show');
                // Posts
                Route::get('posts', [\App\Http\Controllers\Admin\Forum\PostController::class, 'index'])->name('posts.index');
                Route::get('posts/{post}', [\App\Http\Controllers\Admin\Forum\PostController::class, 'show'])->name('posts.show');
                Route::get('posts/{post}/edit', [\App\Http\Controllers\Admin\Forum\PostController::class, 'edit'])->name('posts.edit');
                Route::patch('posts/{post}', [\App\Http\Controllers\Admin\Forum\PostController::class, 'update'])->name('posts.update');
                Route::patch('posts/{post}/approve', [\App\Http\Controllers\Admin\Forum\PostController::class, 'approve'])->name('posts.approve');
                Route::patch('posts/{post}/reject', [\App\Http\Controllers\Admin\Forum\PostController::class, 'reject'])->name('posts.reject');
                Route::delete('posts/{post}', [\App\Http\Controllers\Admin\Forum\PostController::class, 'destroy'])->name('posts.destroy');
                // Comments
                Route::get('comments', [\App\Http\Controllers\Admin\Forum\CommentController::class, 'index'])->name('comments.index');
                Route::patch('comments/{comment}', [\App\Http\Controllers\Admin\Forum\CommentController::class, 'update'])->name('comments.update');
                Route::post('comments/{comment}/reply', [\App\Http\Controllers\Admin\Forum\CommentController::class, 'reply'])->name('comments.reply');
                Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\Forum\CommentController::class, 'destroy'])->name('comments.destroy');
                Route::post('comments/bulk-destroy', [\App\Http\Controllers\Admin\Forum\CommentController::class, 'bulkDestroy'])->name('comments.bulk_destroy');
                Route::patch('comments/{comment}/toggle-hidden', [\App\Http\Controllers\Admin\Forum\CommentController::class, 'toggleHidden'])->name('comments.toggle_hidden');
                // Settings
                Route::get('settings', [\App\Http\Controllers\Admin\Forum\SettingController::class, 'index'])->name('settings.index');
                Route::post('settings', [\App\Http\Controllers\Admin\Forum\SettingController::class, 'update'])->name('settings.update');
            });

            // ===== FAQ MODULE =====
            Route::prefix('faq')->name('faq.')->group(function () {
                // Categories
                Route::resource('categories', \App\Http\Controllers\Admin\Faq\CategoryController::class)->except('show');
                // Articles
                Route::resource('articles', \App\Http\Controllers\Admin\Faq\ArticleController::class)->except('show');
                // Comments
                Route::get('comments', [\App\Http\Controllers\Admin\Faq\CommentController::class, 'index'])->name('comments.index');
                Route::patch('comments/{comment}', [\App\Http\Controllers\Admin\Faq\CommentController::class, 'update'])->name('comments.update');
                Route::post('comments/{comment}/reply', [\App\Http\Controllers\Admin\Faq\CommentController::class, 'reply'])->name('comments.reply');
                Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\Faq\CommentController::class, 'destroy'])->name('comments.destroy');
                Route::post('comments/bulk-destroy', [\App\Http\Controllers\Admin\Faq\CommentController::class, 'bulkDestroy'])->name('comments.bulk_destroy');
                Route::patch('comments/{comment}/toggle-hidden', [\App\Http\Controllers\Admin\Faq\CommentController::class, 'toggleHidden'])->name('comments.toggle_hidden');
                // Settings
                Route::get('settings', [\App\Http\Controllers\Admin\Faq\SettingController::class, 'index'])->name('settings.index');
                Route::post('settings', [\App\Http\Controllers\Admin\Faq\SettingController::class, 'update'])->name('settings.update');
            });

            // ===== MODULE CREDIT =====
            // Gói Credit (CRUD)
            Route::resource('credit-packages', \App\Http\Controllers\Admin\CreditPackageController::class)->except('show');
            // Giao dịch
            Route::get('transactions', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
            Route::get('transactions/{transaction}', [\App\Http\Controllers\Admin\TransactionController::class, 'show'])->name('transactions.show');
            Route::patch('transactions/{transaction}/status', [\App\Http\Controllers\Admin\TransactionController::class, 'updateStatus'])->name('transactions.update-status');
            // VIP
            Route::get('vips', [\App\Http\Controllers\Admin\VipController::class, 'index'])->name('vips.index');
            Route::get('vips/create', [\App\Http\Controllers\Admin\VipController::class, 'create'])->name('vips.create');
            Route::post('vips', [\App\Http\Controllers\Admin\VipController::class, 'store'])->name('vips.store');
            Route::get('vips/{vip}', [\App\Http\Controllers\Admin\VipController::class, 'show'])->name('vips.show');
            Route::get('vips/{vip}/edit', [\App\Http\Controllers\Admin\VipController::class, 'edit'])->name('vips.edit');
            Route::put('vips/{vip}', [\App\Http\Controllers\Admin\VipController::class, 'update'])->name('vips.update');
            Route::delete('vips/{vip}', [\App\Http\Controllers\Admin\VipController::class, 'destroy'])->name('vips.destroy');
            // Cấu hình thanh toán (chỉ super-admin — chứa secret PayPal)
            Route::middleware('check_role:'.UserRole::ADMIN->value)->group(function () {
                Route::get('payment-settings', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'index'])->name('payment_settings.index');
                Route::post('payment-settings', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'update'])->name('payment_settings.update');
            });
        });
});
require __DIR__.'/auth.php';


// Route guest
// SEO public
Route::get('/robots.txt', [\App\Http\Controllers\SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [\App\Http\Controllers\SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/sitemap-pages.xml', [\App\Http\Controllers\SeoController::class, 'sitemapPages'])->name('seo.sitemap.pages');
Route::get('/sitemap-genres.xml', [\App\Http\Controllers\SeoController::class, 'sitemapGenres'])->name('seo.sitemap.genres');
Route::get('/sitemap-authors.xml', [\App\Http\Controllers\SeoController::class, 'sitemapAuthors'])->name('seo.sitemap.authors');
Route::get('/sitemap-articles.xml', [\App\Http\Controllers\SeoController::class, 'sitemapArticles'])->name('seo.sitemap.articles');
Route::get('/sitemap-chapters-{page}.xml', [\App\Http\Controllers\SeoController::class, 'sitemapChapters'])->where('page', '[0-9]+')->name('seo.sitemap.chapters');

// đổi ngôn ngữ
Route::get('/locale/{locale}', function (string $locale) {
    if (array_key_exists($locale, config('locales.supported', []))) {
        session()->put('locale', $locale);
        cookie()->queue(cookie('locale', $locale, 60 * 24 * 365));
    }
    return redirect()->back();
})->name('locale.switch');

// home
Route::get('/',
    [HomeController::class, 'index'])
    ->name('home.index');
Route::get('/search',
    [HomeController::class, 'search'])
    ->name('home.search');
Route::get('/most-read',
    [HomeController::class, 'showHotArticles'])
    ->name('home.show_hot_articles');
Route::get('/latest-updates',
    [HomeController::class, 'showNewUpdateArticles'])
    ->name('home.show_new_update_articles');
Route::get('/completed',
    [HomeController::class, 'showCompletedArticles'])
    ->name('home.show_completed_articles');
// catalog (lọc truyện)
Route::get('/catalog',
    [App\Http\Controllers\Client\CatalogController::class, 'index'])
    ->name('catalog.index');
// live search (instant)
Route::get('/ajax/search-live',
    [App\Http\Controllers\Client\CatalogController::class, 'liveSearch'])
    ->name('catalog.live_search');
// trang tĩnh
Route::get('/faq', [App\Http\Controllers\Client\Faq\CategoryController::class, 'index'])->name('pages.faq');
Route::get('/faq/{faqCategory}', [App\Http\Controllers\Client\Faq\CategoryController::class, 'show'])->name('pages.faq.topic');
Route::get('/faq/{faqCategory}/{faqArticle}', [App\Http\Controllers\Client\Faq\ArticleController::class, 'show'])
    ->name('pages.faq.article');
Route::get('/forum', [App\Http\Controllers\Client\PageController::class, 'forum'])->name('pages.forum');
Route::get('/forum/{category}/{post}', [App\Http\Controllers\Client\PageController::class, 'forumPost'])
    ->name('pages.forum.post');
Route::get('/forum/{category}', [App\Http\Controllers\Client\PageController::class, 'forumCategory'])
    ->name('pages.forum.category');
Route::get('/rules', [App\Http\Controllers\Client\PageController::class, 'rules'])->name('pages.rules');
Route::get('/dmca', [App\Http\Controllers\Client\PageController::class, 'dmca'])->name('pages.dmca');
Route::get('/terms', [App\Http\Controllers\Client\PageController::class, 'terms'])->name('pages.terms');
Route::get('/feedback', [App\Http\Controllers\Client\PageController::class, 'feedback'])->name('pages.feedback');
Route::get('/pricing', [App\Http\Controllers\Client\PageController::class, 'pricing'])->name('pages.pricing');

// PayPal webhook (no auth, no CSRF — excluded in VerifyCsrfToken)
Route::post('/paypal/webhook', [App\Http\Controllers\PaypalController::class, 'webhook'])->name('paypal.webhook');

// PayPal checkout (yêu cầu đăng nhập)
Route::middleware('auth')->group(function () {
    Route::post('/static-pages/{staticPage}/comments', [App\Http\Controllers\Client\StaticPageCommentController::class, 'store'])
        ->name('static-pages.comments.store');
    Route::delete('/static-pages/{staticPage}/comments/{comment}', [App\Http\Controllers\Client\StaticPageCommentController::class, 'destroy'])
        ->name('static-pages.comments.destroy');
    // Forum post CRUD (user)
    Route::get('/forum/{category}/new-post', [App\Http\Controllers\Client\ForumPostController::class, 'create'])->name('forum.posts.create');
    Route::post('/forum/{category}/new-post', [App\Http\Controllers\Client\ForumPostController::class, 'store'])->name('forum.posts.store');
    Route::get('/forum/{category}/{post}/edit', [App\Http\Controllers\Client\ForumPostController::class, 'edit'])->name('forum.posts.edit');
    Route::patch('/forum/{category}/{post}', [App\Http\Controllers\Client\ForumPostController::class, 'update'])->name('forum.posts.update');
    Route::delete('/forum/{category}/{post}', [App\Http\Controllers\Client\ForumPostController::class, 'destroy'])->name('forum.posts.destroy');
    Route::get('/checkout/{creditPackage}', [App\Http\Controllers\PaypalController::class, 'checkout'])->name('checkout.show');
    Route::post('/paypal/create-order',  [App\Http\Controllers\PaypalController::class, 'createOrder'])->name('paypal.create_order');
    Route::post('/paypal/capture-order', [App\Http\Controllers\PaypalController::class, 'captureOrder'])->name('paypal.capture_order');
});
// genres
Route::get('/genres/{genre}',
    [App\Http\Controllers\Client\GenreController::class, 'show'])
    ->name('genres.show');
// articles
Route::get('/book/ajax/chapter-pagination',
    [App\Http\Controllers\Client\ArticleController::class, 'chapterPagination'])
    ->name('articles.chapter_pagination');
Route::get('/articles/{article}',
    [App\Http\Controllers\Client\ArticleController::class, 'show'])
    ->name('articles.show');
Route::get('/articles/{article}/chapters/{number}',
    [\App\Http\Controllers\Client\ChapterController::class, 'show'])
    ->name('articles.chapters.show');
Route::post('articles/{article}/chapters/{number}/mark-ad-clicked', [\App\Http\Controllers\Client\ChapterController::class, 'markAdClicked'])->name('articles.chapters.markAdClicked');
Route::post('articles/{article}/chapters/{number}/unlock', [\App\Http\Controllers\Client\ChapterController::class, 'unlock'])->name('articles.chapters.unlock')->middleware('auth');
Route::post('articles/{article}/chapters/{number}/bookmark-paragraph', [\App\Http\Controllers\Client\ChapterController::class, 'bookmarkParagraph'])->name('articles.chapters.bookmarkParagraph')->middleware('auth');
Route::post('articles/{article}/chapters/{number}/report', [\App\Http\Controllers\Client\ChapterController::class, 'report'])->name('articles.chapters.report')->middleware('auth');
Route::post('articles/{article}/chapters/{number}/like', [\App\Http\Controllers\Client\ChapterController::class, 'likeChapter'])->name('articles.chapters.like')->middleware('auth');
// authors
Route::get('/authors/{author}',
    [\App\Http\Controllers\Client\AuthorController::class, 'show'])
    ->name('authors.show');
// characters (trang công khai: truyện nhân vật xuất hiện)
Route::get('/character/{character}',
    [\App\Http\Controllers\Client\CharacterController::class, 'show'])
    ->name('characters.show');
// users
Route::get('/users/{user}/posted-articles',
    [UserAuthController::class, 'showPostedArticles'])
    ->name('users.show_posted_articles');
Route::get('/users/{user}/bookmarks',
    [UserAuthController::class, 'showBookmarks'])
    ->name('users.show_bookmarks');
Route::get('/users/{user?}', [UserAuthController::class, 'show'])
    ->name('users.show.profile');
Route::get('/users/{user}/comments',
    [UserAuthController::class, 'showComments'])
    ->name('users.show_comments');
// các tab profile bổ sung (clone novelight)
Route::get('/users/{user}/notifications', [UserAuthController::class, 'notifications'])->name('users.notifications');
Route::get('/users/{user}/collections', [UserAuthController::class, 'collections'])->name('users.collections');
Route::get('/users/{user}/teams', [UserAuthController::class, 'teams'])->name('users.teams');
Route::get('/users/{user}/favourites', [UserAuthController::class, 'favourites'])->name('users.favourites');
Route::get('/users/{user}/transactions', [UserAuthController::class, 'transactions'])->name('users.transactions');
Route::get('/users/{user}/achievements', [UserAuthController::class, 'achievements'])->name('users.achievements');
Route::get('/users/{user}/suggestions', [UserAuthController::class, 'suggestions'])->name('users.suggestions');
Route::get('/users/{user}/banlist', [UserAuthController::class, 'banlist'])->name('users.banlist');
Route::get('/users/{user}/history', [UserAuthController::class, 'readingHistory'])->name('users.reading_history');

Route::get('/paypoints', [PaymentController::class, 'showPaypoints'])
    ->name('client.paypoints')
    ->middleware('auth');


Route::post('/set-login-reason', function (\Illuminate\Http\Request $request) {
    session()->put('login_reason', $request->reason);
    return response()->json(['success' => true]);
})->name('setLoginReason');

Route::get('/api/affiliate-popup', function () {
    $link = DB::table('affiliate_links')->inRandomOrder()->first();

    if (!$link) {
        return response()->json(['status' => 'error', 'message' => 'No affiliate links found'], 404);
    }

    return response()->json([
        'status' => 'ok',
        'link' => $link->link,
        'image' => $link->image_path,
    ]);
})->name('get.affiliate.popup');
