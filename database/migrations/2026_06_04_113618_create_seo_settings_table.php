<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seo_settings')) {
            Schema::create('seo_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }

        $defaults = [
            ['key' => 'site_name',           'value' => config('app.name', 'Truyen Tranh'), 'group' => 'general'],
            ['key' => 'title_separator',     'value' => ' · ',                              'group' => 'general'],
            ['key' => 'default_description', 'value' => 'Đọc truyện online: light novel, web novel, ngôn tình, tiên hiệp. Cập nhật nhanh nhất.', 'group' => 'general'],
            ['key' => 'default_keywords',    'value' => 'đọc truyện, light novel, web novel, truyện online', 'group' => 'general'],
            ['key' => 'default_og_image',    'value' => '/static/core/images/no_cover.webp', 'group' => 'general'],
            ['key' => 'og_locale',           'value' => 'vi_VN',                            'group' => 'social'],
            ['key' => 'facebook_page_url',   'value' => '',                                 'group' => 'social'],
            ['key' => 'facebook_app_id',     'value' => '',                                 'group' => 'social'],
            ['key' => 'twitter_username',    'value' => '',                                 'group' => 'social'],
            ['key' => 'google_analytics_id', 'value' => '',                                 'group' => 'advanced'],
            ['key' => 'google_tag_manager',  'value' => '',                                 'group' => 'advanced'],
            ['key' => 'google_site_verify',  'value' => '',                                 'group' => 'advanced'],
            ['key' => 'bing_site_verify',    'value' => '',                                 'group' => 'advanced'],
            ['key' => 'robots_txt_extra',    'value' => '',                                 'group' => 'advanced'],
        ];

        foreach ($defaults as $row) {
            DB::table('seo_settings')->updateOrInsert(
                ['key' => $row['key']],
                ['value' => $row['value'], 'group' => $row['group'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
    }
};
