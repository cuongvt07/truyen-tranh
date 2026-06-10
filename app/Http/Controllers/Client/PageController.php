<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\StaticPage;

class PageController extends Controller
{
    private function pageSetting(string $key, string $default): string
    {
        $value = setting($key);
        return ($value === null || trim($value) === '') ? $default : $value;
    }

    private function localizedPageSetting(string $key, array $defaults): string
    {
        $locale = app()->getLocale();
        $value = setting("{$key}_{$locale}");

        if ($value === null || trim($value) === '') {
            $value = setting($key);
        }

        if ($value === null || trim($value) === '') {
            $value = $defaults[$locale] ?? $defaults['en'] ?? reset($defaults);
        }

        return $value;
    }

    private function staticPage(string $slug): ?StaticPage
    {
        return StaticPage::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    private function pageByTypeAndSlug(string $type, string $slug, ?StaticPage $parent = null): ?StaticPage
    {
        return StaticPage::active()
            ->approved()
            ->where('page_type', $type)
            ->where('slug', $slug)
            ->when($parent, fn ($query) => $query->where('parent_id', $parent->id))
            ->first();
    }

    private function childPages(?StaticPage $parent, string $type, bool $requireApproved = false)
    {
        return StaticPage::active()
            ->when($requireApproved, fn ($q) => $q->approved())
            ->where('page_type', $type)
            ->when($parent, fn ($query) => $query->where('parent_id', $parent->id))
            ->withCount(['comments', 'children'])
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    private function render(string $title, string $icon, string $content)
    {
        return view('client.pages.static', [
            'pageTitle'   => $title,
            'pageIcon'    => $icon,
            'pageContent' => $content,
        ]);
    }

    public function faq()
    {
        $root = $this->pageByTypeAndSlug('faq', 'faq') ?: $this->staticPage('faq');
        $categories = $this->childPages($root, 'faq_category')->get();

        if ($categories->isNotEmpty()) {
            return view('client.pages.faq-index', [
                'pageTitle' => $root ? $root->localizedTitle() : 'FAQ',
                'pageContent' => $root ? $root->localizedContent() : '',
                'page' => $root,
                'categories' => $categories,
            ]);
        }

        if ($page = $this->staticPage('faq')) {
            return view('client.pages.faq', [
                'pageTitle' => $page->localizedTitle(),
                'pageContent' => $page->localizedContent(),
                'page' => $page,
            ]);
        }

        return view('client.pages.faq', [
            'pageTitle' => $this->localizedPageSetting('page_faq_title', [
                'en' => 'Answers to frequently asked questions and problems',
                'vi' => 'Câu hỏi thường gặp và các vấn đề phổ biến',
            ]),
            'pageContent' => $this->localizedPageSetting('page_faq_content', [
                'en' => $this->defaultFaqIndexContent('en'),
                'vi' => $this->defaultFaqIndexContent('vi'),
            ]),
        ]);
    }

    public function faqTopic(string $topic)
    {
        if ($category = $this->pageByTypeAndSlug('faq_category', $topic)) {
            // Lấy tất cả faq_category siblings cho sidebar
            $allCategories = StaticPage::active()
                ->where('page_type', 'faq_category')
                ->orderBy('sort_order')
                ->with(['children' => fn ($q) => $q->active()->where('page_type', 'faq_article')->orderBy('sort_order')])
                ->get();

            return view('client.pages.faq-category', [
                'category'      => $category,
                'articles'      => $this->childPages($category, 'faq_article')->paginate(20),
                'allCategories' => $allCategories,
            ]);
        }

        abort_unless(in_array($topic, ['1', '2', 'account', 'general'], true), 404);

        $normalized = in_array($topic, ['1', 'account'], true) ? 'account' : 'general';
        if ($page = $this->staticPage($normalized)) {
            return view('client.pages.faq-topic', [
                'activeTopic' => $normalized,
                'pageTitle'   => $page->localizedTitle(),
                'pageContent' => $page->localizedContent(),
                'page'        => $page,
            ]);
        }

        $defaults = [
            'account' => [
                'title'   => ['en' => 'Account', 'vi' => 'Tài khoản'],
                'content' => ['en' => $this->defaultFaqTopicContent('account', 'en'), 'vi' => $this->defaultFaqTopicContent('account', 'vi')],
            ],
            'general' => [
                'title'   => ['en' => 'General', 'vi' => 'Chung'],
                'content' => ['en' => $this->defaultFaqTopicContent('general', 'en'), 'vi' => $this->defaultFaqTopicContent('general', 'vi')],
            ],
        ];

        return view('client.pages.faq-topic', [
            'activeTopic' => $normalized,
            'pageTitle'   => $this->localizedPageSetting("page_faq_{$normalized}_title", $defaults[$normalized]['title']),
            'pageContent' => $this->localizedPageSetting("page_faq_{$normalized}_content", $defaults[$normalized]['content']),
        ]);
    }

    public function faqArticle(string $category, string $article)
    {
        $categoryPage = $this->pageByTypeAndSlug('faq_category', $category);
        abort_unless($categoryPage, 404);

        $articlePage = $this->pageByTypeAndSlug('faq_article', $article, $categoryPage);
        abort_unless($articlePage, 404);

        $articlePage->increment('view_count');

        // Sidebar: tất cả categories + articles của chúng
        $allCategories = StaticPage::active()
            ->where('page_type', 'faq_category')
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->active()->where('page_type', 'faq_article')->orderBy('sort_order')])
            ->get();

        $comments = $articlePage->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate(20);

        return view('client.pages.faq-article', [
            'category'      => $categoryPage,
            'article'       => $articlePage->fresh(),
            'comments'      => $comments,
            'allCategories' => $allCategories,
        ]);
    }

    public function forum()
    {
        $root       = $this->pageByTypeAndSlug('forum', 'forum') ?: $this->staticPage('forum');
        $categories = $this->childPages($root, 'forum_category')->get();

        if ($categories->isNotEmpty()) {
            // Lấy post mới nhất cho mỗi category để hiển thị trên forum index
            $categoryIds = $categories->pluck('id');
            $latestPosts = \App\Models\StaticPage::active()->approved()
                ->where('page_type', 'forum_post')
                ->whereIn('parent_id', $categoryIds)
                ->with('author:id,name,username')
                ->orderByDesc('created_at')
                ->get(['id', 'parent_id', 'title_en', 'title_vi', 'slug', 'created_at', 'user_id'])
                ->groupBy('parent_id')
                ->map(fn ($group) => $group->first());

            // Nhóm categories theo section_label để render đúng design
            $sections = $categories->groupBy(fn ($c) => $c->localizedSectionLabel() ?: '__none__');

            return view('client.pages.forum-index', [
                'pageTitle'   => $root ? $root->localizedTitle() : 'Forum',
                'pageContent' => $root ? $root->localizedContent() : '',
                'page'        => $root,
                'categories'  => $categories,
                'sections'    => $sections,
                'latestPosts' => $latestPosts,
            ]);
        }

        if ($page = $this->staticPage('forum')) {
            return view('client.pages.forum', [
                'pageTitle'   => $page->localizedTitle(),
                'pageContent' => $page->localizedContent(),
                'page'        => $page,
            ]);
        }

        return view('client.pages.forum', [
            'pageTitle'   => $this->localizedPageSetting('page_forum_title', ['en' => 'Forum', 'vi' => 'Diễn đàn']),
            'pageContent' => $this->localizedPageSetting('page_forum_content', [
                'en' => $this->defaultForumContent('en'),
                'vi' => $this->defaultForumContent('vi'),
            ]),
        ]);
    }

    public function forumCategory(string $category)
    {
        $categoryPage = $this->pageByTypeAndSlug('forum_category', $category);
        abort_unless($categoryPage, 404);

        return view('client.pages.forum-category', [
            'category' => $categoryPage,
            'posts'    => $this->childPages($categoryPage, 'forum_post', requireApproved: true)
                ->with('author:id,name,username,avatar')
                ->paginate(20),
        ]);
    }

    public function forumPost(string $category, string $post)
    {
        $categoryPage = $this->pageByTypeAndSlug('forum_category', $category);
        abort_unless($categoryPage, 404);

        // Bài approved hiển thị mọi người; bài pending chỉ tác giả và admin thấy
        $postPage = StaticPage::active()
            ->where('page_type', 'forum_post')
            ->where('slug', $post)
            ->where('parent_id', $categoryPage->id)
            ->first();

        abort_unless($postPage, 404);

        // Nếu bài chưa duyệt, chỉ tác giả / admin mới được xem
        if (!$postPage->isApproved()) {
            $user = auth()->user();
            abort_unless(
                $user && ($user->id === $postPage->user_id || $user->is_admin),
                404
            );
        }

        $postPage->increment('view_count');

        $comments = $postPage->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate(20);

        return view('client.pages.forum-post', [
            'category' => $categoryPage,
            'post'     => $postPage->fresh(),
            'comments' => $comments,
        ]);
    }

    public function rules()
    {
        if ($page = $this->staticPage('rules')) {
            $comments = $page->comments()
                ->whereNull('parent_id')
                ->with(['user', 'replies.user'])
                ->latest()
                ->paginate(20);

            return view('client.pages.rules', [
                'pageTitle' => $page->localizedTitle(),
                'pageContent' => $page->localizedContent(),
                'page' => $page,
                'comments' => $comments,
            ]);
        }

        return view('client.pages.rules', [
            'pageTitle' => $this->localizedPageSetting('page_rules_title', [
                'en' => 'General Site Rules and Ban Reasons',
                'vi' => 'Nội quy chung và lý do khóa tài khoản',
            ]),
            'pageContent' => $this->localizedPageSetting('page_rules_content', [
                'en' => $this->defaultRulesContent('en'),
                'vi' => $this->defaultRulesContent('vi'),
            ]),
        ]);
    }

    public function dmca()
    {
        $app = config('app.name');
        $content = <<<HTML
<p>$app tôn trọng quyền sở hữu trí tuệ. Nếu bạn là chủ sở hữu bản quyền và cho rằng nội dung trên website vi phạm, vui lòng gửi yêu cầu gỡ bỏ kèm các thông tin sau:</p>
<ul>
    <li>Thông tin liên hệ của bạn (tên, email).</li>
    <li>Mô tả tác phẩm có bản quyền bị vi phạm.</li>
    <li>Đường dẫn cụ thể tới nội dung vi phạm trên website.</li>
    <li>Tuyên bố rằng bạn là chủ sở hữu hoặc được uỷ quyền hợp pháp.</li>
</ul>
<p>Chúng tôi sẽ xem xét và xử lý trong thời gian sớm nhất.</p>
HTML;
        return $this->render('DMCA', '<i class="fa fa-shield-halved"></i>', $content);
    }

    public function terms()
    {
        $app = config('app.name');
        $content = <<<HTML
<h2>1. Chấp nhận điều khoản</h2>
<p>Khi sử dụng $app, bạn đồng ý tuân thủ các điều khoản dưới đây.</p>
<h2>2. Tài khoản người dùng</h2>
<p>Bạn chịu trách nhiệm bảo mật thông tin tài khoản và mọi hoạt động phát sinh từ tài khoản của mình.</p>
<h2>3. Nội dung</h2>
<p>Nội dung truyện thuộc về tác giả/đơn vị dịch tương ứng. Người dùng không được sao chép, phân phối lại khi chưa được phép.</p>
<h2>4. Thanh toán</h2>
<p>Các giao dịch nạp xu và mua VIP là tự nguyện và không hoàn lại, trừ trường hợp lỗi hệ thống.</p>
HTML;
        return $this->render('Điều khoản sử dụng', '<i class="fa fa-file-contract"></i>', $content);
    }

    public function pricing()
    {
        $pkgs = CreditPackage::active()->get();
        $creditPkgs = $pkgs->where('package_type', 'credit');
        $subscriptionPkgs = $pkgs->where('package_type', 'subscription');
        $featuredPkg = $creditPkgs->firstWhere('is_featured', true) ?? $creditPkgs->get(2) ?? $creditPkgs->first();

        $coinPacks = $creditPkgs->map(fn($p) => [
            'id'        => $p->id,
            'name'      => $p->name,
            'coins'     => $p->coins,
            'price'     => $p->display_price,
            'price_usd' => (float) $p->price_usd,
            'icon'      => $p->icon ?? 'media/payments/1000.jpg',
        ])->toArray();

        $featured = $featuredPkg ? [
            'name'      => $featuredPkg->name,
            'coins'     => $featuredPkg->coins,
            'price'     => $featuredPkg->display_price,
            'price_usd' => (float) $featuredPkg->price_usd,
            'icon'      => $featuredPkg->icon ?? 'media/payments/1000.jpg',
        ] : ($coinPacks[0] ?? null);

        $premiumPackages = getPremiumPackages();
        $premium = !empty($premiumPackages) ? [
            'name'  => $premiumPackages[0]['name'] ?? 'Thành viên Premium',
            'desc'  => ($premiumPackages[0]['days'] ?? 30) . ' ngày',
            'price' => number_format(($premiumPackages[0]['coins'] ?? 700), 0, ',', '.') . ' xu',
            'icon'  => 'media/payments/1.webp',
        ] : [
            'name'  => 'Thành viên Premium',
            'desc'  => '30 ngày',
            'price' => '70.000đ',
            'icon'  => 'media/payments/1.webp',
        ];

        $subscription = $subscriptionPkgs->firstWhere('is_featured', true) ?? $subscriptionPkgs->first();
        if ($subscription) {
            $premium = [
                'id'        => $subscription->id,
                'name'      => $subscription->name,
                'desc'      => $subscription->subscription_days . ' ngày, ẩn quảng cáo + ' . number_format($subscription->daily_credits) . ' credit/ngày',
                'price'     => $subscription->display_price,
                'price_usd' => (float) $subscription->price_usd,
                'icon'      => $subscription->icon ?? 'media/payments/1.webp',
            ];
        }

        return view('client.pages.pricing', compact('coinPacks', 'premium', 'featured'));
    }

    public function feedback()
    {
        $content = <<<HTML
<p>Chúng tôi luôn lắng nghe ý kiến của bạn để cải thiện trải nghiệm đọc truyện.</p>
<p>Nếu bạn gặp lỗi, có đề xuất tính năng, hoặc muốn yêu cầu truyện mới, vui lòng liên hệ qua email hỗ trợ hoặc để lại bình luận trong các trang truyện tương ứng.</p>
HTML;
        return $this->render('Góp ý', '<i class="fa fa-comment-dots"></i>', $content);
    }

    private function defaultForumContent(string $locale = 'en'): string
    {
        if ($locale === 'vi') {
            return <<<'HTML'
<div class="forum-section-name block">THÔNG TIN TỪ BAN QUẢN TRỊ</div>
<div class="forum-themes">
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#news-and-announcements">Tin tức và thông báo</a></h2>
                <div class="description">Theo dõi các cập nhật mới nhất của website.</div>
            </div>
        </div>
    </div>
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#bugs-and-issues">Lỗi và sự cố</a></h2>
                <div class="description">Báo lỗi, sự cố và các vấn đề liên quan đến website.</div>
            </div>
        </div>
    </div>
</div>
<div class="forum-section-name block">CHUNG</div>
<div class="forum-themes">
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#communication">Trò chuyện</a></h2>
                <div class="description">Không gian trao đổi chung của cộng đồng.</div>
            </div>
        </div>
    </div>
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#team-recruitment">Tuyển thành viên nhóm dịch</a></h2>
                <div class="description">Tìm thành viên tham gia các nhóm dịch.</div>
            </div>
        </div>
    </div>
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#articles">Bài viết</a></h2>
                <div class="description">Tin tức thú vị về light novel và cộng đồng đọc truyện.</div>
            </div>
        </div>
    </div>
</div>
HTML;
        }

        return <<<'HTML'
<div class="forum-section-name block">DEVELOPERS' INFORMATION</div>
<div class="forum-themes">
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#news-and-announcements">News and Announcements</a></h2>
                <div class="description">Stay up to date with the latest news about our website updates!</div>
            </div>
        </div>
    </div>
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#bugs-and-issues">Bugs and Issues</a></h2>
                <div class="description">Problems, bugs and errors related to the site</div>
            </div>
        </div>
    </div>
</div>
<div class="forum-section-name block">GENERAL</div>
<div class="forum-themes">
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#communication">Communication</a></h2>
                <div class="description">Just talking with our whole family</div>
            </div>
        </div>
    </div>
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#team-recruitment">Team Recruitment</a></h2>
                <div class="description">Search for members to join the team of translators</div>
            </div>
        </div>
    </div>
    <div class="forum-theme forum-post block">
        <div class="icon"><i class="fa fa-comments"></i></div>
        <div class="forum-theme__info">
            <div class="info">
                <h2 class="title"><a href="/forum#articles">Articles</a></h2>
                <div class="description">Interesting news from the world of light novels</div>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    private function defaultFaqIndexContent(string $locale = 'en'): string
    {
        if ($locale === 'vi') {
            return <<<'HTML'
<div class="faq-theme-blocks">
    <a href="/faq/account" class="block">
        <h2 class="title">Tài khoản</h2>
        <div class="meta-color"><i class="fa fa-newspaper"></i> 2 bài viết</div>
    </a>
    <a href="/faq/general" class="block">
        <h2 class="title">Chung</h2>
        <div class="meta-color"><i class="fa fa-newspaper"></i> 2 bài viết</div>
    </a>
</div>
HTML;
        }

        return <<<'HTML'
<div class="faq-theme-blocks">
    <a href="/faq/account" class="block">
        <h2 class="title">Account</h2>
        <div class="meta-color"><i class="fa fa-newspaper"></i> 2 Articles</div>
    </a>
    <a href="/faq/general" class="block">
        <h2 class="title">General</h2>
        <div class="meta-color"><i class="fa fa-newspaper"></i> 2 Articles</div>
    </a>
</div>
HTML;
    }

    private function defaultFaqTopicContent(string $topic, string $locale = 'en'): string
    {
        if ($topic === 'account') {
            if ($locale === 'vi') {
                return <<<'HTML'
<ul class="content__topic-list">
    <li><a href="/faq/account#google-password">Làm sao biết mật khẩu sau khi đăng ký bằng Google?</a></li>
    <li><a href="/faq/account#cannot-post">Tôi không thể bình luận, đánh giá hoặc đăng truyện sau khi đăng ký</a></li>
</ul>
HTML;
            }

            return <<<'HTML'
<ul class="content__topic-list">
    <li><a href="/faq/account#google-password">How can I find out my login password (after registering via Google)</a></li>
    <li><a href="/faq/account#cannot-post">I cannot post comments, reviews, or books after registering</a></li>
</ul>
HTML;
        }

        if ($locale === 'vi') {
            return <<<'HTML'
<ul class="content__topic-list">
    <li><a href="/faq/general#contact-moderator">Làm sao liên hệ moderator hoặc quản trị viên?</a></li>
    <li><a href="/faq/general#comments-disabled">Vì sao một số truyện bị tắt bình luận?</a></li>
</ul>
HTML;
        }

        return <<<'HTML'
<ul class="content__topic-list">
    <li><a href="/faq/general#contact-moderator">How can I contact a moderator or an administrator?</a></li>
    <li><a href="/faq/general#comments-disabled">Why are comments disabled on some titles?</a></li>
</ul>
HTML;
    }

    private function defaultRulesContent(string $locale = 'en'): string
    {
        if ($locale === 'vi') {
            return <<<'HTML'
<p>Nội quy này áp dụng cho toàn bộ nội dung do người dùng tạo, bao gồm bình luận, avatar, ảnh nền hồ sơ và tên hiển thị. Vi phạm có thể dẫn đến việc xoá nội dung hoặc hạn chế tài khoản tạm thời.</p>
<p>Các lệnh khóa áp dụng cho tính năng cộng đồng của website, nghĩa là bạn có thể không thể:</p>
<ul>
    <li>Đăng bình luận;</li>
    <li>Tham gia thảo luận trên diễn đàn;</li>
    <li>Chỉnh sửa thông tin hồ sơ.</li>
</ul>
<p><b>Lưu ý:</b> Thời hạn khóa được liệt kê là mức tối thiểu. Vi phạm lặp lại có thể bị tăng thời hạn khóa.</p>
<p>Moderator có quyền xoá toàn bộ chuỗi bình luận hoặc chủ đề diễn đàn khi cần thiết.</p>
<hr>
<h2>1. Xúc phạm (khóa từ 7 ngày trở lên)</h2>
<h3>1.1. Nghiêm cấm mọi hình thức xúc phạm, bao gồm:</h3>
<ul>
    <li>Xúc phạm trực tiếp;</li>
    <li>Xúc phạm bóng gió hoặc dùng từ ngữ né tránh;</li>
    <li>Xúc phạm dưới dạng câu hỏi;</li>
    <li>Xúc phạm nhóm người, quốc tịch, chủng tộc, quan điểm chính trị hoặc tôn giáo;</li>
    <li>Xúc phạm gia đình hoặc người thân của người khác;</li>
    <li>Hình ảnh mang tính xúc phạm;</li>
    <li>Phát ngôn thù ghét, phân biệt chủng tộc hoặc bài ngoại.</li>
</ul>
<h3>1.2. Xúc phạm tác giả hoặc dịch giả</h3>
<p>Góp ý mang tính xây dựng được chấp nhận nếu bình tĩnh và tôn trọng. Phàn nàn vô lý về tiến độ chương có thể bị xem là quấy rối.</p>
<hr>
<h2>2. Bình luận vô nghĩa / Spam / Lạc đề (khóa từ 1 ngày trở lên)</h2>
<ul>
    <li>Bình luận không có nội dung và làm loãng thảo luận;</li>
    <li>Lạm dụng CAPS LOCK hoặc emoji;</li>
    <li>Hỏi lặp lại về chương mới khi chưa quá một tháng;</li>
    <li>Nội dung lặp, tin nhắn trùng hoặc flood sau khi ra chương;</li>
    <li>Bình luận không liên quan đến chương hoặc tác phẩm đang thảo luận.</li>
</ul>
<hr>
<h2>3. Spoiler (khóa từ 2 ngày trở lên)</h2>
<p>Spoiler là tiết lộ tình tiết trước khi người khác đọc chương. Spoiler chỉ được chấp nhận nếu liên quan đến chương hiện tại.</p>
<hr>
<h2>4. Quảng cáo và spam (khóa từ 3 ngày trở lên)</h2>
<ul>
    <li>Link tới website cạnh tranh hoặc tài nguyên ngoài;</li>
    <li>Nhắc đến nhóm hoặc nền tảng khác khi chưa được duyệt;</li>
    <li>Kêu gọi người dùng truy cập, đăng ký hoặc bình chọn trên nền tảng ngoài;</li>
    <li>Quảng cáo sản phẩm, dịch vụ hoặc giveaway chưa được ban quản trị cho phép.</li>
</ul>
<hr>
<h2>5. Khiêu khích và gây xung đột (khóa từ 3 ngày trở lên)</h2>
<ul>
    <li>Tôn giáo, chính trị hoặc hệ tư tưởng nếu không thuộc nội dung tác phẩm;</li>
    <li>Khiêu khích hoặc cố tình gây xung đột giữa người dùng.</li>
</ul>
<hr>
<h2>6. Từ ngữ thô tục</h2>
<ul>
    <li>Cho phép ở mức vừa phải nếu không chiếm phần lớn nội dung;</li>
    <li>Lạm dụng từ ngữ thô tục sẽ bị xử lý như spam.</li>
</ul>
<hr>
<h2>7. Dùng tài khoản phụ (khóa vĩnh viễn)</h2>
<p>Tạo hoặc dùng tài khoản phụ để né lệnh khóa sẽ dẫn đến khóa vĩnh viễn tất cả tài khoản liên quan. Sao chép nội dung từ website cũng bị cấm.</p>
<hr>
<h2>8. Avatar và ảnh nền hồ sơ (hạn chế chỉnh sửa từ 3 tháng trở lên)</h2>
<ul>
    <li>Nội dung khiêu dâm hoặc hình ảnh gây phản cảm;</li>
    <li>Nội dung gây khó chịu như hình nhấp nháy hoặc chuyển động quá nhanh.</li>
</ul>
<hr>
<h2>9. Chia sẻ dữ liệu cá nhân (khóa từ 7 ngày trở lên)</h2>
<p>Dữ liệu cá nhân gồm ảnh, địa chỉ, số điện thoại, email, họ tên đầy đủ và các thông tin có thể định danh khác.</p>
<p><b>Hãy nhớ:</b> Tuân thủ nội quy giúp cộng đồng văn minh và dễ chịu hơn cho tất cả mọi người.</p>
HTML;
        }

        return <<<'HTML'
<p>These rules apply to all user-generated content, including comments, avatars, profile backgrounds, and usernames. Violations may result in content removal and temporary restrictions on making changes.</p>
<p>Bans apply to the social features of the site, meaning you will not be able to:</p>
<ul>
    <li>Post comments;</li>
    <li>Participate in forum discussions;</li>
    <li>Edit profile information.</li>
</ul>
<p><b>Important:</b> Ban durations listed are minimums. Repeated offenses will result in doubled ban durations.</p>
<p>Moderators reserve the right to delete entire comment threads or forum topics.</p>
<hr>
<h2>1. Insults (Ban duration: 7 days or more)</h2>
<h3>1.1. Any form of insult is prohibited, including:</h3>
<ul>
    <li>Direct insults;</li>
    <li>Veiled insults or equivalent abuse in any language;</li>
    <li>Insults framed as questions;</li>
    <li>Insults directed at groups, nationalities, races, or political/religious views;</li>
    <li>Insults targeting family members or loved ones;</li>
    <li>Insulting imagery;</li>
    <li>Hate speech, racism, or xenophobia.</li>
</ul>
<h3>1.2. Insults towards authors or translators</h3>
<p>Constructive criticism is welcome if expressed calmly and respectfully. Complaints about chapter delays without valid reasons may be treated as harassment.</p>
<hr>
<h2>2. Meaningless Comments / Spam / Off-topic (Ban duration: 1 day or more)</h2>
<ul>
    <li>Comments without substance that disrupt discussions;</li>
    <li>Excessive CAPS LOCK or emoji use;</li>
    <li>Repeated inquiries about chapter updates unless the delay exceeds one month;</li>
    <li>Long repetitive text, duplicate messages, or flooding after releases;</li>
    <li>Comments unrelated to the chapter or work being discussed.</li>
</ul>
<hr>
<h2>3. Spoilers (Ban duration: 2 days or more)</h2>
<p>Spoilers are plot details revealed before the chapter is read. Spoilers are allowed only if they pertain to the current chapter.</p>
<hr>
<h2>4. Advertising and Spam (Ban duration: 3 days or more)</h2>
<ul>
    <li>Links to competing websites or external resources;</li>
    <li>References to other teams or platforms without approval;</li>
    <li>Soliciting users to visit, subscribe, or vote on external platforms;</li>
    <li>Advertising products, services, or giveaways not approved by the administration.</li>
</ul>
<hr>
<h2>5. Provocations and Conflicts (Ban duration: 3 days or more)</h2>
<ul>
    <li>Religion, politics, or ideologies unless they are part of the work's storyline;</li>
    <li>Provocations or attempts to start conflicts among users.</li>
</ul>
<hr>
<h2>6. Profanity</h2>
<ul>
    <li>Profanity is allowed if it does not dominate the text;</li>
    <li>Excessive, unjustified profanity will be treated as spam.</li>
</ul>
<hr>
<h2>7. Use of Alternate Accounts (Permanent ban)</h2>
<p>Creating or using secondary accounts to evade an active ban will result in permanent banning of all accounts. Copying content from the site is prohibited.</p>
<hr>
<h2>8. Avatars and Profile Backgrounds (Restricted editing: 3 months or more)</h2>
<ul>
    <li>Pornographic material or offensive visuals;</li>
    <li>Content that causes discomfort, such as flashing or rapidly animated images.</li>
</ul>
<hr>
<h2>9. Sharing Personal Data (Ban duration: 7 days or more)</h2>
<p>Personal data includes photos, addresses, phone numbers, emails, full names, and other identifiable information.</p>
<p><b>Remember:</b> Following these rules keeps the community respectful and enjoyable for everyone.</p>
HTML;
    }
}
