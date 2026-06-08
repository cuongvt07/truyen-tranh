<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use Illuminate\Http\Request;

class CreditPackageController extends Controller
{
    public function index()
    {
        $packages = CreditPackage::orderBy('sort_order')->orderBy('coins')->paginate(20);
        return view('admin.credit-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.credit-packages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'coins'         => 'required|integer|min:1',
            'price_vnd'     => 'required|integer|min:0',
            'price_usd'     => 'nullable|numeric|min:0',
            'price_display' => 'nullable|string|max:50',
            'icon'          => 'nullable|string|max:500',
            'is_featured'   => 'boolean',
            'sort_order'    => 'integer|min:0',
            'is_active'     => 'boolean',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');
        $data['price_usd']   = $data['price_usd'] ?? 0;

        CreditPackage::create($data);

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Thêm gói thành công!');
    }

    public function edit(CreditPackage $creditPackage)
    {
        return view('admin.credit-packages.edit', compact('creditPackage'));
    }

    public function update(Request $request, CreditPackage $creditPackage)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'coins'         => 'required|integer|min:1',
            'price_vnd'     => 'required|integer|min:0',
            'price_usd'     => 'nullable|numeric|min:0',
            'price_display' => 'nullable|string|max:50',
            'icon'          => 'nullable|string|max:500',
            'is_featured'   => 'boolean',
            'sort_order'    => 'integer|min:0',
            'is_active'     => 'boolean',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');
        $data['price_usd']   = $data['price_usd'] ?? 0;

        $creditPackage->update($data);

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Cập nhật gói thành công!');
    }

    public function destroy(CreditPackage $creditPackage)
    {
        $creditPackage->delete();
        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Đã xóa gói!');
    }
}
