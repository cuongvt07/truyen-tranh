<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RequireLoginCustomRedirect
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->routeIs('client.paypoints')) {
                if (session('login_reason') === 'from_chapter_buy_vip') {
                    session()->forget('login_reason');
                    session()->put('url.intended', route('client.paypoints'));
                } else {
                    session()->put('url.intended', route('home'));
                }
            } else {
                session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để tiếp tục.');
        }
        return $next($request);
    }
}
