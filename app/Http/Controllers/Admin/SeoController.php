<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return back()->with('success', __('messages.flash.seo_saved'));
    }
}
