<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force https only when the canonical URL is https (behind TLS/Cloudflare).
        // IP-only / plain-http deployments (no domain yet) keep http so assets resolve.
        if ($this->app->environment('production')
            && str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Event::listen(
            \SePay\SePay\Events\SePayWebhookEvent::class,
            \App\Listeners\SePayWebhookListener::class,
        );

        // Share navGenres to all views using the novelight layout
        \Illuminate\Support\Facades\View::composer('layout.novelight', function ($view) {
            $view->with('navGenres', \App\Models\Genre::orderBy('name')->get());
        });
    }
}
