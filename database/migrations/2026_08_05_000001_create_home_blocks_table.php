<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // Nguồn truyện: trending | hot | new_update | latest | exclusive | genre
            $table->string('source', 32)->default('genre');
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('limit')->default(9);
            // Kiểu hiển thị: rail (hàng ngang) | trending (lưới có số thứ tự)
            $table->string('variant', 16)->default('rail');
            $table->boolean('show_see_all')->default(true);
            // Ghi đè link "See All"; để trống thì suy ra từ source/genre.
            $table->string('url')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Trang chủ luôn truy vấn theo đúng cặp này.
            $table->index(['is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_blocks');
    }
};
