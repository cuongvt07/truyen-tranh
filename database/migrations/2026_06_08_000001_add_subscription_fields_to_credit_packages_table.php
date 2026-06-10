<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_packages', function (Blueprint $table) {
            $table->string('package_type', 30)->default('credit')->after('name');
            $table->unsignedSmallInteger('subscription_days')->default(0)->after('coins');
            $table->unsignedInteger('daily_credits')->default(0)->after('subscription_days');
        });
    }

    public function down(): void
    {
        Schema::table('credit_packages', function (Blueprint $table) {
            $table->dropColumn(['package_type', 'subscription_days', 'daily_credits']);
        });
    }
};
