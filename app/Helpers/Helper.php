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
