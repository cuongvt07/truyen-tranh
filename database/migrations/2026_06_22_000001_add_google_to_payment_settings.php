<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            // Google OAuth login config (admin-editable, fallback .env).
            if (!Schema::hasColumn('payment_settings', 'google_client_id')) {
                $table->string('google_client_id')->nullable()->after('paypal_live_webhook_id'); // công khai (frontend)
            }
            if (!Schema::hasColumn('payment_settings', 'google_client_secret')) {
                $table->text('google_client_secret')->nullable()->after('google_client_id'); // mã hoá at-rest
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach (['google_client_id', 'google_client_secret'] as $col) {
                if (Schema::hasColumn('payment_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
