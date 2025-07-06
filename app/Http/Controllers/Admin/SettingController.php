<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = DB::table('settings')->pluck('meta_value', 'meta_key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'logo_file', 'site_name', 'bank1_qr_image', 'bank2_qr_image']);

        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['meta_key' => $key],
                ['meta_value' => $value]
            );
        }

            // --- Lưu ảnh logo ---
        if ($request->hasFile('logo_file')) {
            $logo = $request->file('logo_file');
            $logoPath = $logo->store('logo', 'public'); // lưu vào storage/app/public/logo
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'logo_file'],
                ['meta_value' => $logoPath]
            );
        } else {
            // Nếu không có file, lấy từ URL nếu có
            if ($request->filled('logo_url')) {
                DB::table('settings')->updateOrInsert(
                    ['meta_key' => 'logo_url'],
                    ['meta_value' => $request->input('logo_url')]
                );
            }
        }

        if ($request->hasFile('bank1_qr_image')) {
            $path1 = $request->file('bank1_qr_image')->store('bank_qr', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'bank1_qr_image'],
                ['meta_value' => $path1]
            );
        }

        if ($request->hasFile('bank2_qr_image')) {
            $path2 = $request->file('bank2_qr_image')->store('bank_qr', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'bank2_qr_image'],
                ['meta_value' => $path2]
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'Cập nhật thành công!');
    }
}
