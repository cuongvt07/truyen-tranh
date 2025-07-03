<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositsTable extends Migration
{
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Liên kết với người dùng
            $table->decimal('amount', 10, 2); // Số tiền nạp
            $table->string('payment_method')->default('sepay'); // Cổng thanh toán (SePay, Momo, v.v.)
            $table->string('transaction_id')->unique(); // Mã giao dịch từ cổng thanh toán
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending'); // Trạng thái giao dịch
            $table->string('payment_reference')->nullable(); // Tham chiếu giao dịch từ cổng thanh toán
            $table->string('content')->nullable(); // Thông tin bổ sung (nếu có)
            $table->timestamps(); // Thời gian tạo và cập nhật giao dịch

            // Tạo khóa ngoại cho user_id (nếu bạn muốn tham chiếu tới bảng users)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposits');
    }
}
