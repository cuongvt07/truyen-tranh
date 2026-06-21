<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('static_page_comments')) {
            Schema::create('static_page_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('static_page_id')->constrained('static_pages')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('static_page_comments')->cascadeOnDelete();
                $table->text('content');
                $table->integer('score')->default(0);
                $table->boolean('is_hidden')->default(false)->index();
                $table->timestamps();
            });
        }

        foreach (['comments', 'forum_comments', 'faq_comments', 'static_page_comments'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'is_hidden')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_hidden')->default(false)->after('score')->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['comments', 'forum_comments', 'faq_comments', 'static_page_comments'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_hidden')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropIndex(['is_hidden']);
                });
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_hidden');
                });
            }
        }
    }
};
