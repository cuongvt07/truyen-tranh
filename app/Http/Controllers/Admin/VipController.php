<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVip;
use Illuminate\Http\Request;

class VipController extends Controller
{
    public function index(Request $request)
    {
        $query = UserVip::with('user')->orderByDesc('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', fn($u) => $u->where('username', 'like', "%{$s}%")
                                                    ->orWhere('email', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('end_at', '>=', now());
            } elseif ($request->status === 'expired') {
                $query->where('end_at', '<', now());
            }
        }

        $vips = $query->paginate(25)->withQueryString();

        $stats = [
            'total'   => UserVip::count(),
            'active'  => UserVip::where('end_at', '>=', now())->count(),
            'expired' => UserVip::where('end_at', '<', now())->count(),
        ];

        return view('admin.vips.index', compact('vips', 'stats'));
    }

    public function create()
    {
        $users = User::orderBy('username')->get(['id', 'username', 'email']);
        return view('admin.vips.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'package_name' => 'required|string|max:255',
            'package_days' => 'required|integer|min:1',
            'package_coins'=> 'required|integer|min:0',
            'start_at'     => 'required|date',
        ]);

        $start = \Carbon\Carbon::parse($data['start_at']);
        $end   = $start->copy()->addDays($data['package_days']);

        UserVip::create([
            'user_id'       => $data['user_id'],
            'package_name'  => $data['package_name'],
            'package_days'  => $data['package_days'],
            'package_coins' => $data['package_coins'],
            'start_at'      => $start,
            'end_at'        => $end,
        ]);

        return redirect()->route('admin.vips.index')
            ->with('success', __('messages.flash.vip.granted'));
    }

    public function destroy(UserVip $vip)
    {
        $vip->delete();
        return redirect()->route('admin.vips.index')
            ->with('success', __('messages.flash.vip.revoked'));
    }
}
