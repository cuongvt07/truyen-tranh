<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_settings')) {
            return;
        }

        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('paypal_mode', 10)->default('sandbox'); // sandbox | live
            // client_id công khai (dùng ở frontend) → lưu thường.
            $table->string('paypal_sandbox_client_id')->nullable();
            $table->string('paypal_live_client_id')->nullable();
            // secret + webhook_id nhạy cảm → cột text vì giá trị đã mã hoá dài hơn.
            $table->text('paypal_sandbox_secret')->nullable();
            $table->text('paypal_sandbox_webhook_id')->nullable();
            $table->text('paypal_live_secret')->nullable();
            $table->text('paypal_live_webhook_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
