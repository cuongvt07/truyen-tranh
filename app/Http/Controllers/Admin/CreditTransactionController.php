<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;

class CreditTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditTransaction::query()
            ->with(['user:id,username,name', 'admin:id,username,name'])
            ->latest('id');

        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->whereHas('user', function ($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type') && array_key_exists($request->type, CreditTransaction::TYPES)) {
            $query->where('type', $request->type);
        }

        $items = $query->paginate(40)->withQueryString();

        return view('admin.credit-transactions.index', [
            'items'   => $items,
            'filters' => $request->only(['search', 'type']),
            'types'   => CreditTransaction::TYPES,
        ]);
    }
}
