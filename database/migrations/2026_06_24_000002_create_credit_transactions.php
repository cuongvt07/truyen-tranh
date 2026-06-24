<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_transactions')) {
            return;
        }

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');                 // +cộng / -trừ
            $table->integer('balance_after');          // số dư sau giao dịch
            $table->string('type', 40);                // nguồn: purchase, daily_subscription, chapter_unlock...
            $table->string('description', 255)->nullable();
            $table->nullableMorphs('reference');       // tham chiếu tuỳ chọn (deposit/chapter/...)
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete(); // ai chỉnh (nếu admin)
            $table->timestamps();

            $table->index(['user_id', 'id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
