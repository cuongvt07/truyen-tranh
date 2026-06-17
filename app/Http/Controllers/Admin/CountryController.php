<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('sort_order')->get();
        return view('admin.countries.index', compact('countries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'name_en'    => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        Country::create([
            'name'       => $data['name'],
            'name_en'    => $data['name_en'] ?? '',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        return back()->with('success', 'Đã thêm quốc gia.');
    }

    public function update(Request $request, Country $country)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'name_en'    => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $country->update([
            'name'       => $data['name'],
            'name_en'    => $data['name_en'] ?? '',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        return back()->with('success', 'Đã cập nhật.');
    }

    public function destroy(Country $country)
    {
        $country->delete();
        return back()->with('success', 'Đã xoá.');
    }
}
