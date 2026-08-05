<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Title/description cấu hình riêng cho từng trang tĩnh, lưu chung trong bảng
 * seo_settings dưới key "page:<route>:title" / "page:<route>:description".
 * Không dựng bảng mới để còn dùng lại helper seo_setting() và trang admin SEO.
 */
class PageSeo
{
    /** route name => nhãn hiển thị trong admin. */
    public const PAGES = [
        'home.index'                      => 'Trang chủ',
        'catalog.index'                   => 'Danh mục truyện',
        'home.search'                     => 'Tìm kiếm',
        'home.show_hot_articles'          => 'Truyện nổi bật',
        'home.show_new_update_articles'   => 'Mới cập nhật',
        'home.show_completed_articles'    => 'Đã hoàn thành',
        'pages.gifts'                     => 'Gifts',
        'pages.blog'                      => 'Blog',
        'pages.pricing'                   => 'Bảng giá',
        'pages.faq'                       => 'FAQ',
        'authors.index'                   => 'Danh sách tác giả',
    ];

    /** Cache toàn bộ override để không truy vấn lại theo từng lần gọi. */
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            try {
                self::$cache = DB::table('seo_settings')
                    ->where('key', 'like', 'page:%')
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }

        return self::$cache;
    }

    public static function key(string $route, string $field): string
    {
        return "page:{$route}:{$field}";
    }

    public static function get(string $route, string $field): ?string
    {
        $value = self::all()[self::key($route, $field)] ?? null;

        return ($value === null || $value === '') ? null : $value;
    }

    /**
     * Override cho route đang chạy. Trả về ['title' => ?string, 'description' => ?string].
     * Route không nằm trong PAGES thì không có override (vd trang chi tiết truyện,
     * tiêu đề vốn dựng động từ tên truyện).
     */
    public static function current(): array
    {
        $route = optional(request()->route())->getName();

        if (!$route || !array_key_exists($route, self::PAGES)) {
            return ['title' => null, 'description' => null];
        }

        return [
            'title'       => self::get($route, 'title'),
            'description' => self::get($route, 'description'),
        ];
    }
}
