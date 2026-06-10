<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FAQ Categories
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_vi')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_vi')->nullable();
            $table->string('icon', 50)->default('fa-circle-question');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // FAQ Articles
        Schema::create('faq_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('faq_categories')->cascadeOnDelete();
            $table->string('slug')->index();
            $table->string('title_en');
            $table->string('title_vi')->nullable();
            $table->longText('content_en');
            $table->longText('content_vi')->nullable();
            $table->boolean('is_pinned')->default(false)->index();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['category_id', 'slug']);
        });

        // FAQ Comments
        Schema::create('faq_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('faq_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('faq_comments')->cascadeOnDelete();
            $table->text('content');
            $table->integer('score')->default(0);
            $table->unsignedInteger('reply_count')->default(0);
            $table->timestamps();
            $table->index(['article_id', 'created_at']);
        });

        // FAQ Comment Votes
        Schema::create('faq_comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('faq_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('value')->default(1);
            $table->timestamps();
            $table->unique(['comment_id', 'user_id']);
        });

        // FAQ Settings
        Schema::create('faq_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_comment_votes');
        Schema::dropIfExists('faq_comments');
        Schema::dropIfExists('faq_articles');
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('faq_settings');
    }
};
