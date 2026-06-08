<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('coins');
            $table->unsignedInteger('price_vnd');          // giá VND (SePay/chuyển khoản)
            $table->decimal('price_usd', 8, 2)->default(0); // giá USD (PayPal)
            $table->string('price_display')->default('');   // hiển thị tùy chỉnh, vd "50.000đ"
            $table->string('icon')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_packages');
    }
};
