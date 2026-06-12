<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            // NULL = đăng ngay (mặc định, giữ nguyên hành vi cũ).
            // Thời điểm tương lai = hẹn giờ, ẩn khỏi public tới giờ đó.
            $table->timestamp('published_at')->nullable()->after('content')->index();
        });
    }

    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
