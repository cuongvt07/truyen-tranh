<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('static_pages', 'section_label_en')) {
                // Nhãn section hiển thị header đen trên forum index (ví dụ: "DEVELOPERS' INFORMATION")
                $table->string('section_label_en', 120)->nullable()->after('sort_order');
                $table->string('section_label_vi', 120)->nullable()->after('section_label_en');
            }
        });

        // Seed section labels cho các forum_category hiện có
        \Illuminate\Support\Facades\DB::table('static_pages')
            ->whereIn('slug', ['news-and-announcements', 'bugs-and-issues'])
            ->update([
                'section_label_en' => "DEVELOPERS' INFORMATION",
                'section_label_vi' => 'THÔNG TIN TỪ BAN QUẢN TRỊ',
            ]);

        \Illuminate\Support\Facades\DB::table('static_pages')
            ->whereIn('slug', ['communication', 'team-recruitment', 'articles'])
            ->update([
                'section_label_en' => 'GENERAL',
                'section_label_vi' => 'CHUNG',
            ]);
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            foreach (['section_label_en', 'section_label_vi'] as $col) {
                if (Schema::hasColumn('static_pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
