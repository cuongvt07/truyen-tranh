<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserVip;
use Carbon\Carbon;

class BuyPackageVipController extends Controller
{
    public function buyVip(Request $request)
    {
        $packageId = $request->input('package_id');

        $packages = getPremiumPackages();
        if (!isset($packages[$packageId])) {
            return response()->json([
                'success' => false,
                'message' => 'Gói không tồn tại'
            ]);
        }

        $package = $packages[$packageId];
        $days = (int)$package['days'];
        $coins = (int)$package['coins'];
        $user = auth()->user();

        if ($user->points < $coins) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không đủ xu'
            ]);
        }

        // Trừ xu
        $user->points -= $coins;
        $user->save();

        $lastVip = UserVip::where('user_id', $user->id)
            ->where('end_at', '>=', now())
            ->latest('end_at')
            ->first();

        if ($lastVip) {
            $startAt = Carbon::parse($lastVip->end_at);
        } else {
            $startAt = now();
        }

        $endAt = (clone $startAt)->addDays($days);

        UserVip::create([
            'user_id' => $user->id,
            'package_name' => $package['name'],
            'package_days' => $days,
            'package_coins' => $coins,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mua gói ' . $package['name'] . ' thành công!',
            'vip_start' => $startAt->format('d/m/Y'),
            'vip_end' => now()->addDays($days)->format('d/m/Y'),
        ]);
    }
}
