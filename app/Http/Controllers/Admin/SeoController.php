<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PageSeo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeoController extends Controller
{
    /** Trang cài đặt SEO chung. */
    public function settings()
    {
        $settings = DB::table('seo_settings')->pluck('value', 'key')->toArray();
        return view('admin.seo.settings', compact('settings'));
    }

    /** Lưu cài đặt SEO. */
    public function updateSettings(Request $request)
    {
        // Ảnh OG: upload từ máy được ưu tiên hơn URL gõ tay.
        $request->validate(['default_og_image_file' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096']);
        if ($request->hasFile('default_og_image_file')) {
            $request->merge([
                'default_og_image' => 'storage/' . $request->file('default_og_image_file')->store('images/seo', 'public'),
            ]);
        }

        $keys = [
            'site_name', 'title_separator', 'default_description', 'default_keywords', 'default_og_image',
            'og_locale', 'facebook_page_url', 'facebook_app_id', 'twitter_username',
            'google_analytics_id', 'google_tag_manager', 'google_site_verify', 'bing_site_verify', 'robots_txt_extra',
        ];
        foreach ($keys as $k) {
            DB::table('seo_settings')->updateOrInsert(
                ['key' => $k],
                ['value' => (string) $request->input($k, ''), 'updated_at' => now()]
            );
        }

        // Title/description riêng cho từng trang tĩnh. Bỏ trống = dùng lại
        // hành vi mặc định của layout, không ép chuỗi rỗng ra ngoài site.
        $pageSeo = (array) $request->input('page_seo', []);
        foreach (PageSeo::PAGES as $route => $label) {
            foreach (['title', 'description'] as $field) {
                DB::table('seo_settings')->updateOrInsert(
                    ['key' => PageSeo::key($route, $field)],
                    ['value' => trim((string) ($pageSeo[$route][$field] ?? '')), 'updated_at' => now()]
                );
            }
        }

        return back()->with('success', __('messages.flash.seo_saved'));
    }
}
