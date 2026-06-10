<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('static_pages')->nullOnDelete();
            $table->string('page_type', 40)->default('custom')->index();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_vi')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_vi')->nullable();
            $table->boolean('comments_enabled')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_pages');
    }
};
