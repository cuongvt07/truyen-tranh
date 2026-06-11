<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            if (! Schema::hasColumn('ads', 'chapter_inline_count')) {
                $table->unsignedSmallInteger('chapter_inline_count')->default(1)->after('chapter_interval');
            }
            if (! Schema::hasColumn('ads', 'chapter_inline_first_after')) {
                $table->unsignedSmallInteger('chapter_inline_first_after')->default(4)->after('chapter_inline_count');
            }
            if (! Schema::hasColumn('ads', 'chapter_inline_every')) {
                $table->unsignedSmallInteger('chapter_inline_every')->default(8)->after('chapter_inline_first_after');
            }
        });

        if (! Schema::hasTable('ad_items')) {
            Schema::create('ad_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
                $table->string('title');
                $table->string('image_path')->nullable();
                $table->string('image_url')->nullable();
                $table->string('link')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['ad_id', 'is_active', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_items');

        Schema::table('ads', function (Blueprint $table) {
            foreach (['chapter_inline_count', 'chapter_inline_first_after', 'chapter_inline_every'] as $column) {
                if (Schema::hasColumn('ads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
