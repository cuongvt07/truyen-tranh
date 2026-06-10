<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Forum Categories
        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_vi')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_vi')->nullable();
            $table->string('section_label_en', 120)->nullable(); // "DEVELOPERS' INFORMATION", "GENERAL"
            $table->string('section_label_vi', 120)->nullable();
            $table->string('icon', 50)->default('fa-comments'); // Font Awesome class
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Forum Posts
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('forum_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // null = system post
            $table->string('slug')->index();
            $table->string('title_en');
            $table->string('title_vi')->nullable();
            $table->longText('content_en');
            $table->longText('content_vi')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->boolean('is_pinned')->default(false)->index();
            $table->boolean('is_locked')->default(false); // locked = không thể comment
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['category_id', 'slug']);
        });

        // Forum Comments
        Schema::create('forum_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('forum_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('forum_comments')->cascadeOnDelete();
            $table->text('content');
            $table->integer('score')->default(0); // vote cache
            $table->unsignedInteger('reply_count')->default(0);
            $table->timestamps();
            $table->index(['post_id', 'created_at']);
        });

        // Forum Comment Votes
        Schema::create('forum_comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('forum_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('value')->default(1); // 1 hoặc -1
            $table->timestamps();
            $table->unique(['comment_id', 'user_id']);
        });

        // Forum Settings (key-value config table)
        Schema::create('forum_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_comment_votes');
        Schema::dropIfExists('forum_comments');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_categories');
        Schema::dropIfExists('forum_settings');
    }
};
