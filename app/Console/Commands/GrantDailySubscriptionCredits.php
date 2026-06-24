<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserVip;
use Illuminate\Console\Command;

class GrantDailySubscriptionCredits extends Command
{
    protected $signature = 'subscriptions:grant-daily-credits';

    protected $description = 'Grant daily credits for active ad-free subscriptions.';

    public function handle(): int
    {
        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $granted = 0;
        $credits = 0;

        UserVip::query()
            ->where('daily_credits', '>', 0)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->where(function ($query) use ($todayStart) {
                // Chưa được cộng trong hôm nay (chạy 00:00 -> mỗi sub nhận đúng 1 lần/ngày).
                $query->whereNull('last_daily_credit_at')
                    ->orWhere('last_daily_credit_at', '<', $todayStart);
            })
            ->orderBy('id')
            ->chunkById(100, function ($vips) use ($now, &$granted, &$credits) {
                foreach ($vips as $vip) {
                    $amount = (int) $vip->daily_credits;
                    if ($amount <= 0) {
                        continue;
                    }

                    \App\Services\CreditService::adjust($vip->user_id, $amount, 'daily_subscription', ['reference' => $vip]);
                    $vip->forceFill(['last_daily_credit_at' => $now])->save();

                    $granted++;
                    $credits += $amount;
                }
            });

        $this->info("Granted {$credits} credits across {$granted} active subscriptions.");

        return self::SUCCESS;
    }
}
