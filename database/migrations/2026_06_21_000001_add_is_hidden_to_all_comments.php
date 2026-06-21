<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Article comments
        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('score')->index();
        });

        // 2. Forum comments
        Schema::table('forum_comments', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('score')->index();
        });

        // 3. FAQ comments
        Schema::table('faq_comments', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('score')->index();
        });

        // 4. Static page comments
        Schema::table('static_page_comments', function (Blueprint $table) {
            $table->boolean('is_hidden')->default(false)->after('score')->index();
        });
    }

    public function down(): void
    {
        foreach (['comments', 'forum_comments', 'faq_comments', 'static_page_comments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['is_hidden']);
                $table->dropColumn('is_hidden');
            });
        }
    }
};
