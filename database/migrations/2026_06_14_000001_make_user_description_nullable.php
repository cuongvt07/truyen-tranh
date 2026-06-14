<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 'description' (About me) là tuỳ chọn nhưng cột đang NOT NULL không default,
     * nên khi user để trống -> middleware đổi '' thành null -> vi phạm ràng buộc -> 500
     * lúc lưu hồ sơ (kể cả khi chỉ đổi avatar). Cho phép NULL để sửa triệt để.
     * Dùng raw SQL để khỏi phụ thuộc doctrine/dbal.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `users` MODIFY `description` TEXT NULL');
    }

    public function down(): void
    {
        // Đưa null hiện có về '' trước khi siết lại NOT NULL để down không thất bại.
        DB::statement("UPDATE `users` SET `description` = '' WHERE `description` IS NULL");
        DB::statement('ALTER TABLE `users` MODIFY `description` TEXT NOT NULL');
    }
};
