<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            if (!Schema::hasColumn('genres', 'is_hot')) {
                $table->boolean('is_hot')->default(false)->after('name'); // hiện ở dropdown thể loại header
            }
        });
    }

    public function down(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            if (Schema::hasColumn('genres', 'is_hot')) {
                $table->dropColumn('is_hot');
            }
        });
    }
};
