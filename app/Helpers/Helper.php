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

if (!function_exists('route_path')) {
    function route_path(string $name, mixed $parameters = []): string
    {
        $url = route($name, $parameters, false);

        if (str_starts_with($url, 'http:/') && !str_starts_with($url, 'http://')) {
            return substr($url, 5) ?: '/';
        }

        $parts = parse_url($url);

        if (isset($parts['host'])) {
            $path = $parts['path'] ?? '/';
            return isset($parts['query']) ? $path . '?' . $parts['query'] : $path;
        }

        return $url;
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
            return asset('static/core/images/alphanovel/default-cover.jpg');
        }
        // Ưu tiên bản thu nhỏ -500.jpg (nhẹ) cho card trang chủ / trang con; fallback ảnh gốc.
        return cover_thumb_url($img, 500) ?? $img;
    }
}

if (!function_exists('cover_thumb_rel')) {
    /** Đường dẫn tương đối (trong public disk) của bản thu nhỏ -{w}.jpg, hoặc null nếu ảnh không phải /storage/. */
    function cover_thumb_rel(?string $img, int $w = 500): ?string
    {
        if (!$img || !\Illuminate\Support\Str::startsWith($img, '/storage/')) {
            return null;
        }
        $rel = ltrim(\Illuminate\Support\Str::after($img, '/storage/'), '/');
        $dir = trim(pathinfo($rel, PATHINFO_DIRNAME), '.');

        return ($dir !== '' ? $dir . '/' : '') . pathinfo($rel, PATHINFO_FILENAME) . '-' . $w . '.jpg';
    }
}

if (!function_exists('cover_thumb_url')) {
    /** URL "/storage/...-{w}.jpg" nếu bản thu nhỏ ĐÃ tồn tại, ngược lại null. Cache theo request. */
    function cover_thumb_url(?string $img, int $w = 500): ?string
    {
        $thumbRel = cover_thumb_rel($img, $w);
        if ($thumbRel === null) {
            return null;
        }
        static $cache = [];
        if (!array_key_exists($thumbRel, $cache)) {
            $cache[$thumbRel] = \Illuminate\Support\Facades\Storage::disk('public')->exists($thumbRel)
                ? '/storage/' . $thumbRel : null;
        }

        return $cache[$thumbRel];
    }
}

if (!function_exists('cover_make_thumb')) {
    /**
     * Tạo bản thu nhỏ <tên>-{w}.jpg (rộng tối đa {w}px, giữ tỉ lệ, chỉ thu nhỏ) cạnh ảnh gốc.
     * Chỉ xử lý ảnh /storage/ jpg/png/gif/webp(nếu GD hỗ trợ). Trả URL bản thu nhỏ hoặc null.
     */
    function cover_make_thumb(?string $img, int $w = 500): ?string
    {
        $thumbRel = cover_thumb_rel($img, $w);
        if ($thumbRel === null || !function_exists('imagecreatetruecolor')) {
            return null;
        }
        $rel = ltrim(\Illuminate\Support\Str::after($img, '/storage/'), '/');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if (!$disk->exists($rel)) {
            return null;
        }
        $abs = $disk->path($rel);
        $info = @getimagesize($abs);
        if (!$info) {
            return null;
        }
        [$ow, $oh, $type] = $info;
        if ($ow < 1 || $oh < 1) {
            return null;
        }
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($abs),
            IMAGETYPE_PNG  => @imagecreatefrompng($abs),
            IMAGETYPE_GIF  => @imagecreatefromgif($abs),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($abs) : null,
            default        => null,
        };
        if (!$src) {
            return null;
        }
        $nw = min($ow, $w);
        $nh = max(1, (int) round($oh * ($nw / $ow)));
        $dst = imagecreatetruecolor($nw, $nh);
        $white = imagecolorallocate($dst, 255, 255, 255);   // nền trắng cho ảnh trong suốt -> jpg
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
        @imagejpeg($dst, $disk->path($thumbRel), 82);
        imagedestroy($src);
        imagedestroy($dst);

        return $disk->exists($thumbRel) ? '/storage/' . $thumbRel : null;
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

if (!function_exists('user_is_vip')) {
    /**
     * VIP active theo user_id bất kỳ (dùng cho avatar người khác: comment, profile...).
     * Nạp 1 lần tập user_id VIP còn hạn để tránh N+1.
     */
    function user_is_vip(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }
        static $vipIds = null;
        if ($vipIds === null) {
            try {
                $vipIds = \App\Models\UserVip::where('end_at', '>=', now())
                    ->pluck('user_id')->flip()->all();
            } catch (\Throwable $e) {
                $vipIds = [];
            }
        }
        return isset($vipIds[$userId]);
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

if (!function_exists('valid_avatar_url')) {
    /**
     * Dữ liệu seed để lại avatar là câu chữ lorem chứ không phải đường dẫn,
     * nên `?:` không bắt được. Chỉ nhận giá trị trông như URL/đường dẫn ảnh,
     * còn lại trả ảnh mặc định.
     */
    function valid_avatar_url(?string $avatar): string
    {
        $fallback = asset('static/core/images/alphanovel/review-avatar_1.png');
        $avatar = trim((string) $avatar);

        if ($avatar === '' || preg_match('/\s/', $avatar)) {
            return $fallback;
        }

        $looksLikePath = str_starts_with($avatar, '/')
            || str_starts_with($avatar, 'http://')
            || str_starts_with($avatar, 'https://')
            || str_starts_with($avatar, 'storage/');

        return $looksLikePath ? $avatar : $fallback;
    }
}
