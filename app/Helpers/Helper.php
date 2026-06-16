<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Enums\ArticleStatus;

/**
 * Gán class active cho menu.
 */
if (!function_exists('set_active')) {
    function set_active($route): string
    {
        return Route::is($route) ? 'active' : '';
    }
}

/**
 * Kiểm tra route hiện tại.
 */
if (!function_exists('is_route')) {
    function is_route($route): bool
    {
        return Route::is($route);
    }
}

/**
 * Validate trạng thái bài viết.
 */
if (!function_exists('validateArticleStatus')) {
    function validateArticleStatus($status): bool
    {
        if (!is_numeric($status)) {
            return false;
        }

        return ArticleStatus::tryFrom((int)$status) !== null;
    }
}

/**
 * Kiểm tra xem user hiện tại có phải chủ tài khoản.
 */
if (!function_exists('isMyAccount')) {
    function isMyAccount($currentUser, $targetUser): bool
    {
        return !empty($currentUser) && !empty($targetUser) && $currentUser->id === $targetUser->id;
    }
}

/**
 * Lấy 1 giá trị setting theo key.
 */
if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return DB::table('settings')->where('meta_key', $key)->value('meta_value') ?? $default;
    }
}

if (!function_exists('coin_name')) {
    /**
     * Tên đơn vị "xu" hiển thị toàn site — admin cấu hình được THEO NGÔN NGỮ
     * (setting coin_name_en / coin_name_vi...). Trống -> fallback nhãn dịch mặc định.
     */
    function coin_name(): string
    {
        $name = setting('coin_name_' . app()->getLocale());
        return ($name !== null && $name !== '') ? $name : __('messages.pay.coins');
    }
}

/**
 * Lấy danh sách gói ưu đãi premium.
 *
 * @return array
 */
if (!function_exists('getPremiumPackages')) {
    function getPremiumPackages(): array
    {
        $settings = DB::table('settings')
            ->where('meta_key', 'like', 'premium_package_%')
            ->pluck('meta_value', 'meta_key')
            ->toArray();

        $packages = [];

        foreach ($settings as $key => $value) {
            if (preg_match('/^premium_package_(\d+)_name$/', $key, $m)) {
                $index = (int)$m[1];
                $packages[$index] = [
                    'name'  => $value,
                    'coins' => $settings["premium_package_{$index}_coins"] ?? '',
                    'days'  => $settings["premium_package_{$index}_days"] ?? '',
                ];
            }
        }

        ksort($packages);

        return $packages;
    }
}

/**
 * Trả về URL ảnh bìa, fallback về no_picture.jpg nếu trống.
 */
if (!function_exists('novel_poster')) {
    function novel_poster($article): string
    {
        $img = $article->cover_image ?? '';
        // Bỏ qua placeholder faker và các đường dẫn cũ không hợp lệ
        if (!$img || str_contains($img, 'via.placeholder') || str_contains($img, 'placeholder.com')) {
            return asset('static/core/images/no_cover.webp');
        }
        return $img;
    }
}

/**
 * Lấy 1 giá trị SEO setting theo key (cache theo request).
 */
if (!function_exists('seo_setting')) {
    function seo_setting($key, $default = null)
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = DB::table('seo_settings')->pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                $cache = [];
            }
        }
        $val = $cache[$key] ?? null;
        return ($val === null || $val === '') ? $default : $val;
    }
}

/**
 * Trả về URL ảnh nền (background), fallback về cover, rồi no_picture.
 */
if (!function_exists('novel_bg')) {
    function novel_bg($article): string
    {
        return $article->background_image
            ?? $article->cover_image
            ?? asset('static/core/images/no_cover.webp');
    }
}

/**
 * Lấy cây menu theo vị trí (header / browse / footer / mobile) cho layout.
 * Trả về collection rỗng nếu chưa cấu hình -> layout tự fallback.
 */
if (!function_exists('menu_items')) {
    function menu_items(string $location)
    {
        try {
            return \App\Models\Menu::tree($location);
        } catch (\Throwable $e) {
            return collect();
        }
    }
}

/**
 * Tăng version cache sitemap -> các mảnh sitemap tự build lại ở lần truy cập sau.
 */
if (!function_exists('bump_sitemap_version')) {
    function bump_sitemap_version(): void
    {
        try {
            $v = (int) \Illuminate\Support\Facades\Cache::get('sitemap_version', 1);
            \Illuminate\Support\Facades\Cache::forever('sitemap_version', $v + 1);
        } catch (\Throwable $e) {
            // bỏ qua nếu cache lỗi
        }
    }
}

/**
 * Suy ra loại trang hiện tại để chèn quảng cáo.
 */
if (!function_exists('current_ad_page_type')) {
    function current_ad_page_type(): ?string
    {
        $name = optional(Route::current())->getName();
        if ($name === null) {
            return null;
        }
        return match (true) {
            $name === 'articles.chapters.show' => 'chapter',
            $name === 'articles.show'          => 'article',
            $name === 'genres.show'            => 'genre',
            $name === 'catalog.index'          => 'catalog',
            str_starts_with($name, 'home.')    => 'home',
            default                            => null,
        };
    }
}

/**
 * User hiện tại có gói VIP còn hiệu lực không (memoize trong 1 request).
 */
if (!function_exists('user_has_active_vip')) {
    function user_has_active_vip(): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return $cached = false;
        }
        try {
            return $cached = \App\Models\UserVip::where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->where('end_at', '>=', now())
                ->exists();
        } catch (\Throwable $e) {
            return $cached = false;
        }
    }
}

/**
 * Quảng cáo cho trang hiện tại (gom theo dạng chạy: banner|popup|click_anywhere).
 * VIP đang subscribe thì không hiện bất kỳ quảng cáo nào.
 */
if (!function_exists('ads_for')) {
    function ads_for(?string $page = null)
    {
        if (user_has_active_vip()) {
            return collect();
        }
        try {
            return \App\Models\Ad::forPageGrouped($page ?? current_ad_page_type());
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
