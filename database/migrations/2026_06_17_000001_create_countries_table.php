<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->default('');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('countries')->insert([
            ['id' => 1, 'name' => 'Trung Quốc', 'name_en' => 'Chinese',  'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Nhật Bản',   'name_en' => 'Japanese', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Hàn Quốc',   'name_en' => 'Korean',   'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Việt Nam',    'name_en' => 'Vietnamese','sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Âu - Mỹ',    'name_en' => 'Western',  'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'Khác',        'name_en' => 'Other',    'sort_order' => 99,'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
