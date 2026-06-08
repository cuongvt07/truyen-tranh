<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'similar_article_ids')) {
                $table->json('similar_article_ids')->nullable()->after('country');
            }
            if (!Schema::hasColumn('articles', 'translation_request_article_ids')) {
                $table->json('translation_request_article_ids')->nullable()->after('similar_article_ids');
            }
            if (!Schema::hasColumn('articles', 'related_genre_ids')) {
                $table->json('related_genre_ids')->nullable()->after('translation_request_article_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            foreach (['related_genre_ids', 'translation_request_article_ids', 'similar_article_ids'] as $column) {
                if (Schema::hasColumn('articles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
