<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('static_pages', 'status')) {
                // pending | approved | rejected
                $table->string('status', 20)->default('approved')->after('is_active')->index();
            }
            if (!Schema::hasColumn('static_pages', 'user_id')) {
                // null = created by admin (no owner), otherwise = user who posted
                $table->foreignId('user_id')->nullable()->after('parent_id')
                    ->constrained('users')->nullOnDelete();
            }
        });

        // Bài do admin tạo sẵn đều approved
        \Illuminate\Support\Facades\DB::table('static_pages')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            if (Schema::hasColumn('static_pages', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('static_pages', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
