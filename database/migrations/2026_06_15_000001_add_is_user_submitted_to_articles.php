<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đánh dấu truyện do USER tự gửi (qua /dang-truyen) để phân biệt với truyện admin tạo.
     * Dùng cho block "Translation requests" ở home (user gửi đã duyệt) + lọc quản trị admin.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'is_user_submitted')) {
                $table->boolean('is_user_submitted')->default(false)->index()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'is_user_submitted')) {
                $table->dropColumn('is_user_submitted');
            }
        });
    }
};
