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
        $data = $request->except([
            '_token', '_method', 'logo_file', 'favicon_file', 'site_name', 'bank1_qr_image', 'bank2_qr_image',
            'banner_top', 'banner_bottom', 'banner_left', 'banner_right',
            'banner_top_url', 'banner_bottom_url', 'banner_left_url', 'banner_right_url',
            'chapter_footer_image',
        ]);

        // Cập nhật các dữ liệu khác (bảng settings)
        foreach ($data as $key => $value) {
            if (is_array($value)) continue;
            DB::table('settings')->updateOrInsert(
                ['meta_key' => $key],
                ['meta_value' => $value]
            );
        }

        // site_name (đồng bộ cả settings + seo_settings để title SEO dùng chung)
        if ($request->filled('site_name')) {
            DB::table('settings')->updateOrInsert(['meta_key' => 'site_name'], ['meta_value' => $request->input('site_name')]);
            DB::table('seo_settings')->updateOrInsert(['key' => 'site_name'], ['value' => $request->input('site_name'), 'updated_at' => now()]);
        }

        // --- Lưu ảnh logo ---
        if ($request->hasFile('logo_file')) {
            $logo = $request->file('logo_file');
            $logoPath = $logo->store('logo', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'logo_file'],
                ['meta_value' => $logoPath]
            );
        }

        // --- Lưu favicon ---
        if ($request->hasFile('favicon_file')) {
            $fav = $request->file('favicon_file');
            $favPath = $fav->store('logo', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'favicon_file'],
                ['meta_value' => $favPath]
            );
        }

        // --- Lưu ảnh footer chapter ---
        if ($request->hasFile('chapter_footer_image')) {
            $cfImg = $request->file('chapter_footer_image')->store('chapter_footer', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'chapter_footer_image'],
                ['meta_value' => $cfImg]
            );
        }

        // Lưu ảnh QR ngân hàng 1
        if ($request->hasFile('bank1_qr_image')) {
            $path1 = $request->file('bank1_qr_image')->store('bank_qr', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'bank1_qr_image'],
                ['meta_value' => $path1]
            );
        }

        // Lưu ảnh QR ngân hàng 2
        if ($request->hasFile('bank2_qr_image')) {
            $path2 = $request->file('bank2_qr_image')->store('bank_qr', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'bank2_qr_image'],
                ['meta_value' => $path2]
            );
        }

        // Lưu ảnh Banner Top
        if ($request->hasFile('banner_top')) {
            $topBanner = $request->file('banner_top');
            $topBannerPath = $topBanner->store('banners', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_top'],
                ['meta_value' => $topBannerPath]
            );
        }

        // Lưu URL Banner Top (nếu có)
        if ($request->filled('banner_top_url')) {
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_top_url'],
                ['meta_value' => $request->input('banner_top_url')]
            );
        }

        // Lưu ảnh Banner Bottom
        if ($request->hasFile('banner_bottom')) {
            $bottomBanner = $request->file('banner_bottom');
            $bottomBannerPath = $bottomBanner->store('banners', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_bottom'],
                ['meta_value' => $bottomBannerPath]
            );
        }

        // Lưu URL Banner Bottom (nếu có)
        if ($request->filled('banner_bottom_url')) {
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_bottom_url'],
                ['meta_value' => $request->input('banner_bottom_url')]
            );
        }

        // Lưu ảnh Banner Left
        if ($request->hasFile('banner_left')) {
            $leftBanner = $request->file('banner_left');
            $leftBannerPath = $leftBanner->store('banners', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_left'],
                ['meta_value' => $leftBannerPath]
            );
        }

        // Lưu URL Banner Left (nếu có)
        if ($request->filled('banner_left_url')) {
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_left_url'],
                ['meta_value' => $request->input('banner_left_url')]
            );
        }

        // Lưu ảnh Banner Right
        if ($request->hasFile('banner_right')) {
            $rightBanner = $request->file('banner_right');
            $rightBannerPath = $rightBanner->store('banners', 'public');
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_right'],
                ['meta_value' => $rightBannerPath]
            );
        }

        // Lưu URL Banner Right (nếu có)
        if ($request->filled('banner_right_url')) {
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'banner_right_url'],
                ['meta_value' => $request->input('banner_right_url')]
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'Cập nhật thành công!');
    }

}
