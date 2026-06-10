<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Genre;
use App\Models\Slug;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/users';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        Route::bind('article', function ($value) {
            return Slug::resolve('article', $value)
                ?? (is_numeric($value) ? Article::findOrFail($value) : abort(404));
        });

        Route::bind('genre', function ($value) {
            return Slug::resolve('genre', $value)
                ?? (is_numeric($value) ? Genre::findOrFail($value) : abort(404));
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
