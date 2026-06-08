<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Chapter number from which credits are required (null = free for all)
            $table->unsignedSmallInteger('credit_start_chapter')->nullable()->after('is_adult');
            // Default credits per chapter (0 = free)
            $table->unsignedSmallInteger('credit_per_chapter')->default(0)->after('credit_start_chapter');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['credit_start_chapter', 'credit_per_chapter']);
        });
    }
};
