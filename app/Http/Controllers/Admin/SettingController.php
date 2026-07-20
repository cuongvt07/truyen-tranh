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
        $request->validate([
            'guest_articles_per_day' => ['sometimes', 'required', 'integer', 'min:1', 'max:10000'],
            'guest_chapters_per_day' => ['sometimes', 'required', 'integer', 'min:1', 'max:10000'],
            'unpaid_user_chapters' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000000'],
            'daily_checkin_enabled' => ['sometimes', 'required', 'in:0,1'],
            'daily_checkin_default_reward' => ['sometimes', 'required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $data = $request->except([
            '_token', '_method', 'logo_file', 'favicon_file', 'site_name', 'bank1_qr_image',
            'chapter_footer_image',
        ]);

        // Cập nhật các dữ liệu khác (bảng settings)
        foreach ($data as $key => $value) {
            if (is_array($value)) continue;
            if (str_ends_with($key, '_remove')) continue; // cờ xoá ảnh — xử lý riêng bên dưới
            DB::table('settings')->updateOrInsert(
                ['meta_key' => $key],
                ['meta_value' => $value]
            );
        }

        // site_name (đồng bộ cả settings + seo_settings để title SEO dùng chung)
        if ($request->has('daily_checkin_rewards')) {
            $rewardMap = [];
            foreach ((array) $request->input('daily_checkin_rewards', []) as $day => $amount) {
                $day = (int) $day;
                if ($day < 1 || $day > 31 || $amount === null || $amount === '') {
                    continue;
                }
                $rewardMap[$day] = max(0, (int) $amount);
            }
            DB::table('settings')->updateOrInsert(
                ['meta_key' => 'daily_checkin_rewards'],
                ['meta_value' => json_encode($rewardMap)]
            );
        }

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

        // --- Xoá ảnh khi bấm "Xoá ảnh" (cờ {field}_remove = 1, không upload mới) ---
        foreach (['logo_file', 'favicon_file', 'chapter_footer_image', 'bank1_qr_image'] as $imgKey) {
            if (!$request->hasFile($imgKey) && $request->input($imgKey . '_remove') === '1') {
                DB::table('settings')->where('meta_key', $imgKey)->delete();
            }
        }

        return redirect()->route('admin.settings.index')->with('success', __('messages.flash.settings_saved'));
    }

}
