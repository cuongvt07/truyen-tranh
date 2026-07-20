<?php

namespace App\Services;

use App\Models\DailyCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyCheckinService
{
    public function enabled(): bool
    {
        return (string) setting('daily_checkin_enabled', '1') === '1';
    }

    public function defaultReward(): int
    {
        return max(0, (int) setting('daily_checkin_default_reward', 5));
    }

    public function specialRewards(): array
    {
        $raw = setting('daily_checkin_rewards', '{}');
        $decoded = is_string($raw) ? json_decode($raw, true) : [];
        if (!is_array($decoded)) {
            return [];
        }

        $rewards = [];
        foreach ($decoded as $day => $amount) {
            $day = (int) $day;
            if ($day < 1 || $day > 31 || $amount === '' || $amount === null) {
                continue;
            }
            $rewards[$day] = max(0, (int) $amount);
        }

        return $rewards;
    }

    public function rewardForDate(?Carbon $date = null): int
    {
        $date = $date ?: now();
        $day = (int) $date->day;
        $special = $this->specialRewards();

        return array_key_exists($day, $special)
            ? (int) $special[$day]
            : $this->defaultReward();
    }

    public function hasClaimed(User $user, ?Carbon $date = null): bool
    {
        $date = ($date ?: now())->toDateString();

        return DailyCheckin::query()
            ->where('user_id', $user->id)
            ->whereDate('checkin_date', $date)
            ->exists();
    }

    public function calendar(User $user, ?Carbon $month = null): array
    {
        $month = ($month ?: now())->copy()->startOfMonth();
        $today = now()->toDateString();
        $daysInMonth = $month->daysInMonth;
        $special = $this->specialRewards();
        $claimed = DailyCheckin::query()
            ->where('user_id', $user->id)
            ->whereBetween('checkin_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->pluck('amount', 'checkin_date')
            ->toArray();

        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->copy()->day($day);
            $dateKey = $date->toDateString();
            $days[] = [
                'day' => $day,
                'date' => $dateKey,
                'amount' => array_key_exists($day, $special) ? (int) $special[$day] : $this->defaultReward(),
                'special' => array_key_exists($day, $special),
                'claimed' => array_key_exists($dateKey, $claimed),
                'today' => $dateKey === $today,
                'future' => $date->greaterThan(now()->endOfDay()),
            ];
        }

        return [
            'enabled' => $this->enabled(),
            'month_label' => $month->format('F Y'),
            'today' => now()->toDateString(),
            'today_amount' => $this->rewardForDate(now()),
            'claimed_today' => $this->hasClaimed($user, now()),
            'coin_name' => coin_name(),
            'days' => $days,
        ];
    }

    public function claim(User $user): array
    {
        if (!$this->enabled()) {
            return ['claimed' => false, 'already_claimed' => false, 'amount' => 0, 'balance' => (int) $user->points];
        }

        $today = now();
        $date = $today->toDateString();
        $amount = $this->rewardForDate($today);

        $result = DB::transaction(function () use ($user, $date, $today, $amount) {
            $existing = DailyCheckin::query()
                ->where('user_id', $user->id)
                ->whereDate('checkin_date', $date)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $freshUser = User::query()->find($user->id);
                return [
                    'checkin' => $existing,
                    'claimed' => false,
                    'already_claimed' => true,
                    'balance' => (int) ($freshUser->points ?? $user->points),
                ];
            }

            $checkin = DailyCheckin::create([
                'user_id' => $user->id,
                'checkin_date' => $date,
                'day_of_month' => (int) $today->day,
                'amount' => $amount,
            ]);

            if ($amount > 0) {
                CreditService::adjust($user->id, $amount, 'daily_checkin', [
                    'description' => 'Daily check-in reward for ' . $date,
                    'reference' => $checkin,
                ]);
            }

            $freshUser = User::query()->find($user->id);

            return [
                'checkin' => $checkin,
                'claimed' => true,
                'already_claimed' => false,
                'balance' => (int) ($freshUser->points ?? $user->points),
            ];
        });

        return [
            'claimed' => $result['claimed'],
            'already_claimed' => $result['already_claimed'],
            'amount' => $amount,
            'balance' => $result['balance'],
            'calendar' => $this->calendar($user->fresh()),
        ];
    }

    public function promptAfterLogin(): void
    {
        if ($this->enabled()) {
            session()->put('daily_checkin_prompt', true);
        }
    }
}
