<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('static_pages', 'excerpt_en')) {
                $table->text('excerpt_en')->nullable()->after('content_vi');
            }
            if (!Schema::hasColumn('static_pages', 'excerpt_vi')) {
                $table->text('excerpt_vi')->nullable()->after('excerpt_en');
            }
            if (!Schema::hasColumn('static_pages', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('comments_enabled');
            }
            if (!Schema::hasColumn('static_pages', 'view_count')) {
                $table->unsignedBigInteger('view_count')->default(0)->after('is_pinned');
            }
        });

        if (!Schema::hasTable('static_page_comments')) {
            Schema::create('static_page_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('static_page_id')->constrained('static_pages')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('static_page_comments')->cascadeOnDelete();
                $table->text('content');
                $table->integer('score')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('static_page_comments');

        Schema::table('static_pages', function (Blueprint $table) {
            foreach (['excerpt_en', 'excerpt_vi', 'is_pinned', 'view_count'] as $column) {
                if (Schema::hasColumn('static_pages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
