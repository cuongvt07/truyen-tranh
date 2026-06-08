<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('characters')) {
            Schema::create('characters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('photo')->nullable();
                $table->tinyInteger('type')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('photo')->nullable();
                $table->text('description')->nullable();
                $table->string('site')->nullable();
                $table->string('donation_text')->nullable();
                $table->string('donation_url')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('collections')) {
            Schema::create('collections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_private')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('collection_article')) {
            Schema::create('collection_article', function (Blueprint $table) {
                $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
                $table->foreignId('article_id')->constrained()->cascadeOnDelete();
                $table->primary(['collection_id', 'article_id']);
            });
        }

        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('article_tag')) {
            Schema::create('article_tag', function (Blueprint $table) {
                $table->foreignId('article_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->primary(['article_id', 'tag_id']);
            });
        }

        if (!Schema::hasTable('article_character')) {
            Schema::create('article_character', function (Blueprint $table) {
                $table->foreignId('article_id')->constrained()->cascadeOnDelete();
                $table->foreignId('character_id')->constrained()->cascadeOnDelete();
                $table->primary(['article_id', 'character_id']);
            });
        }

        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'is_adult')) {
                $table->boolean('is_adult')->default(false)->after('novel_type');
            }
            if (!Schema::hasColumn('articles', 'illustrator')) {
                $table->string('illustrator')->nullable()->after('alt_title');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_character');
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('collection_article');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('characters');
        Schema::table('articles', function (Blueprint $table) {
            foreach (['is_adult', 'illustrator'] as $c) {
                if (Schema::hasColumn('articles', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
