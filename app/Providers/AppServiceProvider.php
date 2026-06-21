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
        if ($this->app->environment('production')) {
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
