<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            if (! Schema::hasColumn('ads', 'script_code')) {
                $table->mediumText('script_code')->nullable()->after('image_url');
            }
        });

        Schema::table('ad_items', function (Blueprint $table) {
            if (! Schema::hasColumn('ad_items', 'script_code')) {
                $table->mediumText('script_code')->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ad_items', function (Blueprint $table) {
            if (Schema::hasColumn('ad_items', 'script_code')) {
                $table->dropColumn('script_code');
            }
        });

        Schema::table('ads', function (Blueprint $table) {
            if (Schema::hasColumn('ads', 'script_code')) {
                $table->dropColumn('script_code');
            }
        });
    }
};
