<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reply lồng nhau + đếm vote cache trên comment
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('article_id');
                $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            }
            if (!Schema::hasColumn('comments', 'score')) {
                $table->integer('score')->default(0)->after('content');       // tổng up - down (cache)
            }
            if (!Schema::hasColumn('comments', 'replies_count')) {
                $table->unsignedInteger('replies_count')->default(0)->after('score');
            }
        });

        // Phiếu vote (up = 1, down = -1), mỗi user 1 phiếu / comment
        if (!Schema::hasTable('comment_votes')) {
            Schema::create('comment_votes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('comment_id');
                $table->unsignedBigInteger('user_id');
                $table->tinyInteger('value')->default(1);    // 1 hoặc -1
                $table->timestamps();

                $table->unique(['comment_id', 'user_id']);
                $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Báo cáo bình luận
        if (!Schema::hasTable('comment_reports')) {
            Schema::create('comment_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('comment_id');
                $table->unsignedBigInteger('user_id');
                $table->string('reason')->nullable();
                $table->boolean('resolved')->default(false);
                $table->timestamps();

                $table->unique(['comment_id', 'user_id']);
                $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reports');
        Schema::dropIfExists('comment_votes');
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('comments', 'score')) {
                $table->dropColumn('score');
            }
            if (Schema::hasColumn('comments', 'replies_count')) {
                $table->dropColumn('replies_count');
            }
        });
    }
};
