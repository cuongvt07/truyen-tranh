<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = array_keys(config('locales.supported', ['vi' => []]));
        $default   = config('locales.default', 'vi');

        // Ưu tiên: session -> cookie -> trình duyệt -> mặc định
        $locale = session('locale')
            ?? $request->cookie('locale')
            ?? $request->getPreferredLanguage($supported)
            ?? $default;

        if (!in_array($locale, $supported, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        // Định dạng ngày/giờ/số theo locale của thị trường (nếu có)
        $intl = config("locales.supported.$locale.locale");
        if ($intl) {
            setlocale(LC_TIME, $intl . '.UTF-8', $intl);
            \Carbon\Carbon::setLocale($locale);
        }

        return $next($request);
    }
}
