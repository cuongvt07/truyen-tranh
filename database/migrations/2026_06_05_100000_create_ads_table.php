<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ads')) {
            return;
        }

        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // tên nội bộ để quản lý
            $table->string('image_path')->nullable();        // ảnh upload (storage)
            $table->string('image_url')->nullable();         // ảnh ngoài (ưu tiên nếu có)
            $table->string('link')->nullable();              // đích khi click

            // Dạng chạy: banner | click_anywhere | popup | chapter
            $table->string('display_mode')->default('banner');
            // Vị trí slot khi là banner: top|bottom|sidebar|in_content|float_left|float_right
            $table->string('placement')->nullable();

            // Các loại trang được chèn: ['home','article','chapter','genre','catalog'] hoặc ['all']
            $table->json('pages')->nullable();

            // Tần suất (popup / click_anywhere): every_load | once_session | every_n_views
            $table->string('frequency')->default('once_session');
            $table->unsignedInteger('frequency_value')->default(1); // N cho every_n_views
            $table->unsignedInteger('delay_seconds')->default(0);   // đếm ngược trước khi cho đóng (popup)

            // Cấu hình riêng khi đọc chapter
            $table->unsignedInteger('chapter_start')->default(2);    // hiện từ chương N
            $table->unsignedInteger('chapter_interval')->default(1); // mỗi N chương
            $table->boolean('hide_for_vip')->default(true);          // ẩn với user VIP
            $table->boolean('require_click')->default(false);        // phải click mới đọc tiếp

            $table->unsignedInteger('priority')->default(0);         // thứ tự ưu tiên (nhỏ trước)
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
