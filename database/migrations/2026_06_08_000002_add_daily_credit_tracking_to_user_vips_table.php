<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_vips', function (Blueprint $table) {
            $table->unsignedInteger('daily_credits')->default(0)->after('package_coins');
            $table->timestamp('last_daily_credit_at')->nullable()->after('daily_credits');
        });
    }

    public function down(): void
    {
        Schema::table('user_vips', function (Blueprint $table) {
            $table->dropColumn(['daily_credits', 'last_daily_credit_at']);
        });
    }
};
