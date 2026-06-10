<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $forumId = $this->page([
            'page_type' => 'forum',
            'slug' => 'forum',
            'title_en' => 'Forum',
            'title_vi' => 'Diễn đàn',
            'content_en' => '',
            'content_vi' => '',
            'sort_order' => 0,
        ], $now);

        $newsId = $this->page([
            'parent_id' => $forumId,
            'page_type' => 'forum_category',
            'slug' => 'news-and-announcements',
            'title_en' => 'News and Announcements',
            'title_vi' => 'Tin tức và thông báo',
            'excerpt_en' => 'Stay up to date with the latest news about our website updates!',
            'excerpt_vi' => 'Theo dõi các cập nhật mới nhất của website.',
            'sort_order' => 10,
        ], $now);

        $this->page([
            'parent_id' => $forumId,
            'page_type' => 'forum_category',
            'slug' => 'bugs-and-issues',
            'title_en' => 'Bugs and Issues',
            'title_vi' => 'Lỗi và sự cố',
            'excerpt_en' => 'Problems, bugs and errors related to the site',
            'excerpt_vi' => 'Báo lỗi, sự cố và các vấn đề liên quan đến website.',
            'sort_order' => 20,
        ], $now);

        $this->page([
            'parent_id' => $forumId,
            'page_type' => 'forum_category',
            'slug' => 'communication',
            'title_en' => 'Communication',
            'title_vi' => 'Trò chuyện',
            'excerpt_en' => 'Just talking with our whole family',
            'excerpt_vi' => 'Không gian trao đổi chung của cộng đồng.',
            'sort_order' => 30,
        ], $now);

        $this->page([
            'parent_id' => $forumId,
            'page_type' => 'forum_category',
            'slug' => 'team-recruitment',
            'title_en' => 'Team Recruitment',
            'title_vi' => 'Tuyển thành viên',
            'excerpt_en' => 'Search for members to join the team of translators',
            'excerpt_vi' => 'Tìm thành viên tham gia các nhóm dịch.',
            'sort_order' => 40,
        ], $now);

        $this->page([
            'parent_id' => $forumId,
            'page_type' => 'forum_category',
            'slug' => 'articles',
            'title_en' => 'Articles',
            'title_vi' => 'Bài viết',
            'excerpt_en' => 'Interesting news from the world of light novels',
            'excerpt_vi' => 'Tin tức thú vị về light novel và cộng đồng đọc truyện.',
            'sort_order' => 50,
        ], $now);

        $this->page([
            'parent_id' => $newsId,
            'page_type' => 'forum_post',
            'slug' => 'a-book-is-gone-read-this-first',
            'title_en' => 'A book is GONE? Read this first!',
            'title_vi' => 'Không thấy truyện? Đọc bài này trước!',
            'content_en' => '<p><strong>Hello!</strong></p><p>As you know, books can sometimes disappear from the platform for various reasons. Before reporting a book as missing, please try searching for the book title and checking the Chapters section first.</p><p><em>P.S. Please do not advertise other websites here.</em></p>',
            'content_vi' => '<p><strong>Xin chào!</strong></p><p>Một số truyện có thể tạm biến mất vì nhiều lý do. Trước khi báo thiếu truyện, vui lòng thử tìm lại tên truyện và kiểm tra mục Chapters trước.</p><p><em>Lưu ý: vui lòng không quảng cáo website khác tại đây.</em></p>',
            'comments_enabled' => true,
            'is_pinned' => true,
            'sort_order' => 10,
        ], $now);

        $this->page([
            'parent_id' => $newsId,
            'page_type' => 'forum_post',
            'slug' => 'introducing-the-premium-pack',
            'title_en' => 'Introducing the Premium Pack!',
            'title_vi' => 'Giới thiệu gói Premium!',
            'content_en' => '<p>Premium Pack information and update notes can be managed from this admin page.</p>',
            'content_vi' => '<p>Thông tin gói Premium và các ghi chú cập nhật có thể quản trị tại trang này.</p>',
            'comments_enabled' => true,
            'is_pinned' => true,
            'sort_order' => 20,
        ], $now);

        $faqId = $this->page([
            'page_type' => 'faq',
            'slug' => 'faq',
            'title_en' => 'Answers to frequently asked questions and problems',
            'title_vi' => 'Câu hỏi thường gặp và các vấn đề phổ biến',
            'content_en' => '',
            'content_vi' => '',
            'sort_order' => 0,
        ], $now);

        $accountId = $this->page([
            'parent_id' => $faqId,
            'page_type' => 'faq_category',
            'slug' => 'account',
            'title_en' => 'Account',
            'title_vi' => 'Tài khoản',
            'excerpt_en' => 'Login, registration and profile questions.',
            'excerpt_vi' => 'Câu hỏi về đăng nhập, đăng ký và hồ sơ.',
            'sort_order' => 10,
        ], $now);

        $generalId = $this->page([
            'parent_id' => $faqId,
            'page_type' => 'faq_category',
            'slug' => 'general',
            'title_en' => 'General',
            'title_vi' => 'Chung',
            'excerpt_en' => 'General website questions.',
            'excerpt_vi' => 'Các câu hỏi chung về website.',
            'sort_order' => 20,
        ], $now);

        $this->page([
            'parent_id' => $accountId,
            'page_type' => 'faq_article',
            'slug' => 'google-password',
            'title_en' => 'How can I find out my login password after registering via Google?',
            'title_vi' => 'Làm sao biết mật khẩu sau khi đăng ký bằng Google?',
            'content_en' => '<p>If you registered via Google, use the password reset flow with the same email address to create a local password.</p>',
            'content_vi' => '<p>Nếu bạn đăng ký bằng Google, hãy dùng chức năng quên mật khẩu với cùng email để tạo mật khẩu đăng nhập nội bộ.</p>',
            'comments_enabled' => true,
            'sort_order' => 10,
        ], $now);

        $this->page([
            'parent_id' => $generalId,
            'page_type' => 'faq_article',
            'slug' => 'contact-moderator',
            'title_en' => 'How can I contact a moderator or an administrator?',
            'title_vi' => 'Làm sao liên hệ moderator hoặc quản trị viên?',
            'content_en' => '<p>Please use the feedback page or the related discussion thread if you need support.</p>',
            'content_vi' => '<p>Vui lòng dùng trang góp ý hoặc chủ đề thảo luận liên quan nếu bạn cần hỗ trợ.</p>',
            'comments_enabled' => true,
            'sort_order' => 10,
        ], $now);

        $this->page([
            'page_type' => 'rules',
            'slug' => 'rules',
            'title_en' => 'General Site Rules and Ban Reasons',
            'title_vi' => 'Nội quy chung và lý do khóa tài khoản',
            'content_en' => '<p>These rules apply to all user-generated content, including comments, avatars, profile backgrounds, and usernames.</p>',
            'content_vi' => '<p>Nội quy này áp dụng cho toàn bộ nội dung do người dùng tạo, bao gồm bình luận, avatar, ảnh nền hồ sơ và tên hiển thị.</p>',
            'comments_enabled' => true,
            'sort_order' => 0,
        ], $now);
    }

    public function down(): void
    {
        DB::table('static_pages')
            ->whereIn('slug', [
                'news-and-announcements',
                'bugs-and-issues',
                'communication',
                'team-recruitment',
                'articles',
                'a-book-is-gone-read-this-first',
                'introducing-the-premium-pack',
                'account',
                'general',
                'google-password',
                'contact-moderator',
            ])
            ->delete();
    }

    private function page(array $data, $now): int
    {
        $existing = DB::table('static_pages')->where('slug', $data['slug'])->first();

        if ($existing) {
            return (int) $existing->id;
        }

        $defaults = [
            'parent_id' => null,
            'content_en' => null,
            'content_vi' => null,
            'excerpt_en' => null,
            'excerpt_vi' => null,
            'comments_enabled' => false,
            'is_pinned' => false,
            'view_count' => 0,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $insert = array_merge($defaults, $data);
        $insert['slug'] = $insert['slug'] ?: Str::slug($insert['title_en']);

        return (int) DB::table('static_pages')->insertGetId($insert);
    }
};
