<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use App\Models\UserVip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VipController extends Controller
{
    public function index(Request $request)
    {
        $query = UserVip::with('user')->orderByDesc('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', fn($u) => $u->where('username', 'like', "%{$s}%")
                                                    ->orWhere('email', 'like', "%{$s}%")
                                                    ->orWhere('name', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active'  => $query->where('end_at', '>=', now()),
                'expired' => $query->where('end_at', '<', now()),
                default   => null,
            };
        }

        if ($request->filled('package')) {
            $query->where('package_name', 'like', '%' . $request->package . '%');
        }

        $vips     = $query->paginate(25)->withQueryString();
        $packages = UserVip::distinct()->orderBy('package_name')->pluck('package_name');

        $stats = [
            'total'   => UserVip::count(),
            'active'  => UserVip::where('end_at', '>=', now())->count(),
            'expired' => UserVip::where('end_at', '<', now())->count(),
        ];

        return view('admin.vips.index', compact('vips', 'stats', 'packages'));
    }

    public function show(UserVip $vip)
    {
        $vip->load('user');

        // Gói subscription tương ứng (match theo tên)
        $package = \App\Models\CreditPackage::where('package_type', 'subscription')
            ->where('name', $vip->package_name)
            ->first();

        // Deposit liên quan: content chứa tên gói + user + gần thời điểm tạo VIP
        $relatedDeposit = Deposit::where('user_id', $vip->user_id)
            ->where('content', 'like', '%' . $vip->package_name . '%')
            ->where('status', 'completed')
            ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, created_at, ?))', [$vip->created_at])
            ->first();

        // Toàn bộ lịch sử VIP của user
        $allVips = UserVip::where('user_id', $vip->user_id)->orderByDesc('start_at')->get();

        // Lịch sử nạp tiền
        $deposits = Deposit::where('user_id', $vip->user_id)
            ->orderByDesc('created_at')->get();

        return view('admin.vips.show', compact('vip', 'package', 'relatedDeposit', 'allVips', 'deposits'));
    }

    public function create()
    {
        $users = User::orderBy('username')->get(['id', 'username', 'email']);
        return view('admin.vips.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'package_name'  => 'required|string|max:255',
            'package_days'  => 'required|integer|min:1',
            'package_coins' => 'required|integer|min:0',
            'daily_credits' => 'nullable|integer|min:0',
            'start_at'      => 'required|date',
        ]);

        $start = Carbon::parse($data['start_at']);
        UserVip::create([
            'user_id'       => $data['user_id'],
            'package_name'  => $data['package_name'],
            'package_days'  => $data['package_days'],
            'package_coins' => $data['package_coins'],
            'daily_credits' => $data['daily_credits'] ?? 0,
            'start_at'      => $start,
            'end_at'        => $start->copy()->addDays($data['package_days']),
        ]);

        return redirect()->route('admin.vips.index')
            ->with('success', __('messages.flash.vip.granted'));
    }

    public function edit(UserVip $vip)
    {
        $vip->load('user');
        return view('admin.vips.edit', compact('vip'));
    }

    public function update(Request $request, UserVip $vip)
    {
        $data = $request->validate([
            'package_name'  => 'required|string|max:255',
            'package_days'  => 'required|integer|min:1',
            'package_coins' => 'required|integer|min:0',
            'daily_credits' => 'nullable|integer|min:0',
            'start_at'      => 'required|date',
            'end_at'        => 'required|date|after:start_at',
        ]);

        $vip->update([
            'package_name'  => $data['package_name'],
            'package_days'  => $data['package_days'],
            'package_coins' => $data['package_coins'],
            'daily_credits' => $data['daily_credits'] ?? 0,
            'start_at'      => Carbon::parse($data['start_at']),
            'end_at'        => Carbon::parse($data['end_at']),
        ]);

        return redirect()->route('admin.vips.index')
            ->with('success', __('messages.flash.vip.updated'));
    }

    public function destroy(UserVip $vip)
    {
        $vip->delete();
        return redirect()->route('admin.vips.index')
            ->with('success', __('messages.flash.vip.revoked'));
    }
}
