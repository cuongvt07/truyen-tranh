<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'rating')) {
                $table->decimal('rating', 3, 1)->default(0)->after('view');
            }
            if (!Schema::hasColumn('articles', 'rating_count')) {
                $table->unsignedInteger('rating_count')->default(0)->after('rating');
            }
            if (!Schema::hasColumn('articles', 'background_image')) {
                $table->string('background_image')->nullable()->after('cover_image');
            }
            if (!Schema::hasColumn('articles', 'novel_type')) {
                $table->tinyInteger('novel_type')->default(0)->after('is_completed');
            }
            if (!Schema::hasColumn('articles', 'alt_title')) {
                $table->string('alt_title')->nullable()->after('title');
            }
            if (!Schema::hasColumn('articles', 'year_of_release')) {
                $table->year('year_of_release')->nullable()->after('alt_title');
            }
            if (!Schema::hasColumn('articles', 'country')) {
                $table->tinyInteger('country')->nullable()->after('year_of_release');
            }
        });

        Schema::table('genres', function (Blueprint $table) {
            if (!Schema::hasColumn('genres', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $cols = ['rating','rating_count','background_image','novel_type','alt_title','year_of_release','country'];
            $table->dropColumn(array_filter($cols, fn($c) => Schema::hasColumn('articles', $c)));
        });
        Schema::table('genres', function (Blueprint $table) {
            if (Schema::hasColumn('genres', 'cover_image')) {
                $table->dropColumn('cover_image');
            }
        });
    }
};
