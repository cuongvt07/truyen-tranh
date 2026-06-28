<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cộng credit subscription đúng 00:00 mỗi ngày (đầu ngày mới). withoutOverlapping phòng chạy chồng.
        $schedule->command('subscriptions:grant-daily-credits')->dailyAt('00:00')->withoutOverlapping();
        // Báo "chương mới" cho chương hẹn giờ vừa tới giờ đăng (chương đăng ngay đã báo lúc tạo).
        $schedule->command('notifications:new-chapters')->everyFiveMinutes();
        // Seed 10 bình luận/ngày (10 người, 10 comment, 10 truyện khác nhau) — chạy cuối ngày để created_at rải cả ngày.
        $schedule->command('comments:seed-daily --count=10')->dailyAt('22:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
