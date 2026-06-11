<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bookmarks', 'status')) {
            return;
        }

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->string('status', 30)->default('reading')->after('article_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bookmarks', 'status')) {
            return;
        }

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
