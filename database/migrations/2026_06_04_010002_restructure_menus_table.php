<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Chuyển bảng `menus` legacy (name/description/link — dùng cho navbar cũ đã chết)
 * sang dạng theo vị trí (location) cho hệ menu kiểu WordPress.
 * menu_items đã được tạo ở migration trước.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Xoá dữ liệu legacy (navbar cũ không còn render) — dữ liệu mới do seeder tạo
        Schema::disableForeignKeyConstraints();
        DB::table('menu_items')->delete();
        DB::table('menus')->delete();
        Schema::enableForeignKeyConstraints();

        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('menus', 'link')) {
                $table->dropColumn('link');
            }
            if (!Schema::hasColumn('menus', 'location')) {
                $table->string('location')->nullable()->after('id');
            }
            if (!Schema::hasColumn('menus', 'created_at')) {
                $table->timestamps();
            }
        });

        // Đảm bảo unique cho location
        try {
            Schema::table('menus', function (Blueprint $table) {
                $table->unique('location');
            });
        } catch (\Throwable $e) {
            // unique đã tồn tại — bỏ qua
        }
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'location')) {
                $table->dropUnique(['location']);
                $table->dropColumn('location');
            }
            $table->string('description')->nullable();
            $table->string('link')->nullable();
        });
    }
};
