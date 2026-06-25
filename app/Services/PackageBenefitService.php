<?php

namespace App\Services;

use App\Models\CreditPackage;
use App\Models\User;
use App\Models\UserVip;
use App\Services\CreditService;
use Carbon\Carbon;

class PackageBenefitService
{
    public function grant(User $user, CreditPackage $package): array
    {
        if ($package->isSubscription()) {
            return $this->grantSubscription($user, $package);
        }

        CreditService::adjust($user->id, (int) $package->coins, 'purchase', ['reference' => $package]);

        return [
            'type' => 'credit',
            'coins' => $package->coins,
            'message' => "Bạn nhận được {$package->coins} credit.",
        ];
    }

    private function grantSubscription(User $user, CreditPackage $package): array
    {
        $days = max(1, (int) $package->subscription_days);

        $lastVip = UserVip::where('user_id', $user->id)
            ->where('end_at', '>=', now())
            ->latest('end_at')
            ->first();

        $startAt = $lastVip ? Carbon::parse($lastVip->end_at) : now();
        $endAt = (clone $startAt)->addDays($days);

        $vip = UserVip::create([
            'user_id' => $user->id,
            'package_name' => $package->name,
            'package_days' => $days,
            'package_coins' => $package->coins,
            'daily_credits' => (int) $package->daily_credits,
            'last_daily_credit_at' => now(),
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        // Cộng NGAY credit gốc của gói (= daily_credits) khi mua/gia hạn. Các ngày sau do scheduler 00:00 cộng.
        if ((int) $package->daily_credits > 0) {
            CreditService::adjust($user->id, (int) $package->daily_credits, 'subscription_init', ['reference' => $vip]);
        }

        return [
            'type' => 'subscription',
            'vip' => $vip,
            'daily_credits' => (int) $package->daily_credits,
            'initial_credits' => (int) $package->daily_credits,
            'vip_start' => $startAt,
            'vip_end' => $endAt,
            'message' => "Gói subscription {$package->name} đã được kích hoạt.",
        ];
    }
}
