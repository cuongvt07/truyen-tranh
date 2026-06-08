<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            if (!Schema::hasColumn('ads', 'after_click')) {
                // Sau khi user click & link đã chạy: none | stop_session | cooldown
                $table->string('after_click')->default('none')->after('delay_seconds');
            }
            if (!Schema::hasColumn('ads', 'cooldown_seconds')) {
                // Số giây chờ trước khi quảng cáo hoạt động lại (khi after_click = cooldown)
                $table->unsignedInteger('cooldown_seconds')->default(0)->after('after_click');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            foreach (['cooldown_seconds', 'after_click'] as $column) {
                if (Schema::hasColumn('ads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
