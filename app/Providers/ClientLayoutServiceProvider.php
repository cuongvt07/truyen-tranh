<?php

namespace App\Providers;

use App\Models\Genre;
use App\Models\Menu;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\UserVip;
use Carbon\Carbon;

class ClientLayoutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('client.*', function ($view) {
            $links = Menu::all();
            $genres = Genre::all();
            $currentUser = Auth::user();
            
            $isUserLoggedIn = Auth::check();

            $activeVipDays = 0;
            if ($isUserLoggedIn && $currentUser) {
                $currentDate = Carbon::now('Asia/Ho_Chi_Minh');
                $userVips = UserVip::where('user_id', $currentUser->id)
                    ->where('end_at', '>', $currentDate)
                    ->orderBy('end_at')
                    ->get();

                if ($userVips->isNotEmpty()) {
                    $lastEndDate = $currentDate;
                    foreach ($userVips as $vip) {
                        $startDate = max($lastEndDate, Carbon::parse($vip->start_at));
                        $endDate = Carbon::parse($vip->end_at);
                        if ($endDate > $currentDate) {
                            $remainingDays = $startDate->diffInDays($endDate, false);
                            $activeVipDays += $remainingDays;
                            $lastEndDate = $endDate;
                        }
                    }
                }
            }

            $view->with('links', $links)
                ->with('genres', $genres)
                ->with('currentUser', $currentUser)
                ->with('isUserLoggedIn', $isUserLoggedIn)
                ->with('activeVipDays', $activeVipDays);
        });
    }
}