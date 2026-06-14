<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

/**
 * Truy cập bất kỳ URL nào kèm ?clear_cache=1 -> xoá cache toàn site.
 * CHỈ admin mới kích hoạt; người khác thì param bị bỏ qua (không phải vector DoS).
 * Cache (Redis DB 1) tách khỏi session (DB 0) nên xoá cache KHÔNG đăng xuất ai.
 */
class ClearSiteCache
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('clear_cache') && $request->boolean('clear_cache', true)) {
            $user = $request->user();
            if ($user && $user->is_admin) {
                // optimize:clear gồm: cache, config, route, view, compiled, events
                Artisan::call('optimize:clear');

                // Quay lại đúng URL hiện tại, bỏ riêng param clear_cache (giữ param khác)
                $query = $request->query();
                unset($query['clear_cache']);
                $url = $request->url() . (count($query) ? '?' . http_build_query($query) : '');

                return redirect($url)->with('status', '✅ Đã xoá toàn bộ cache (cache/config/route/view).');
            }
            // Không phải admin -> bỏ qua param, xử lý request bình thường.
        }

        return $next($request);
    }
}
