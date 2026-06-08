<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Deposit::with('user')->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_id', 'like', "%{$s}%")
                  ->orWhere('content', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('username', 'like', "%{$s}%")
                                                     ->orWhere('email', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => Deposit::count(),
            'completed' => Deposit::where('status', 'completed')->count(),
            'pending'   => Deposit::where('status', 'pending')->count(),
            'failed'    => Deposit::where('status', 'failed')->count(),
            'revenue'   => Deposit::where('status', 'completed')->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }

    public function show(Deposit $transaction)
    {
        $transaction->load('user');
        return view('admin.transactions.show', compact('transaction'));
    }

    public function updateStatus(Request $request, Deposit $transaction)
    {
        $request->validate(['status' => 'required|in:pending,completed,failed']);

        $old = $transaction->status;
        $transaction->status = $request->status;
        $transaction->save();

        // Nếu vừa chuyển sang completed thì cộng điểm cho user
        if ($old !== 'completed' && $request->status === 'completed') {
            $coins = (int) $transaction->amount;
            if ($transaction->user_id && $coins > 0) {
                User::where('id', $transaction->user_id)->increment('points', $coins);
            }
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
    }
}
