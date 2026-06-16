<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng notifications mặc định của Laravel (database channel).
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // Đánh dấu chương đã gửi noti (tránh gửi trùng; cron dùng để bắt chương hẹn giờ vừa lên).
        Schema::table('chapters', function (Blueprint $table) {
            if (!Schema::hasColumn('chapters', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('published_at');
            }
        });

        // Backfill: đánh dấu MỌI chương HIỆN CÓ là đã báo -> tránh cron gửi noti hàng loạt
        // cho chương cũ. Từ giờ chỉ chương MỚI mới gửi noti.
        DB::table('chapters')->whereNull('notified_at')
            ->update(['notified_at' => DB::raw('COALESCE(published_at, created_at, NOW())')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::table('chapters', function (Blueprint $table) {
            if (Schema::hasColumn('chapters', 'notified_at')) {
                $table->dropColumn('notified_at');
            }
        });
    }
};
