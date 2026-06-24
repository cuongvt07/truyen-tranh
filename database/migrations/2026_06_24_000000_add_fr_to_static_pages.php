<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('static_pages', 'title_fr')) {
                $table->string('title_fr')->nullable()->after('title_vi');
            }
            if (!Schema::hasColumn('static_pages', 'content_fr')) {
                $table->longText('content_fr')->nullable()->after('content_vi');
            }
            if (!Schema::hasColumn('static_pages', 'excerpt_fr')) {
                $table->text('excerpt_fr')->nullable()->after('excerpt_vi');
            }
            if (!Schema::hasColumn('static_pages', 'section_label_fr')) {
                $table->string('section_label_fr')->nullable()->after('section_label_vi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            foreach (['title_fr', 'content_fr', 'excerpt_fr', 'section_label_fr'] as $col) {
                if (Schema::hasColumn('static_pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
