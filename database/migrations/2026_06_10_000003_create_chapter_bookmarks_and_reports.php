<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vị trí đoạn đang đọc dở của user trong một chương (server-side bookmark).
        if (! Schema::hasTable('chapter_paragraph_bookmarks')) {
            Schema::create('chapter_paragraph_bookmarks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('chapter_id');
                $table->unsignedBigInteger('article_id');
                $table->unsignedInteger('paragraph')->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'chapter_id']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
                $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            });
        }

        // Báo cáo lỗi chương (admin xem & xử lý).
        if (! Schema::hasTable('chapter_reports')) {
            Schema::create('chapter_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chapter_id');
                $table->unsignedBigInteger('article_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('reason')->nullable();
                $table->boolean('resolved')->default(false);
                $table->timestamps();

                $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
                $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_reports');
        Schema::dropIfExists('chapter_paragraph_bookmarks');
    }
};
