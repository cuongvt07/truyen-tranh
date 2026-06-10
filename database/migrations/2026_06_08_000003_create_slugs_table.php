<?php

use App\Models\Article;
use App\Models\Genre;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('slugs')) {
            Schema::create('slugs', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50);
                $table->string('slug');
                $table->string('sluggable_type');
                $table->unsignedBigInteger('sluggable_id');
                $table->timestamps();

                $table->unique(['type', 'slug']);
                $table->unique(['type', 'sluggable_type', 'sluggable_id'], 'slugs_type_model_unique');
                $table->index(['sluggable_type', 'sluggable_id']);
            });
        }

        $ensureSlug = function (string $type, string $modelClass, int $modelId, string $source): void {
            $base = Str::slug($source);
            if ($base === '') {
                $base = $type . '-' . $modelId;
            }

            $existing = DB::table('slugs')
                ->where('type', $type)
                ->where('sluggable_type', $modelClass)
                ->where('sluggable_id', $modelId)
                ->first();

            if ($existing && Str::startsWith($existing->slug, $base)) {
                return;
            }

            $slug = $base;
            $suffix = 2;

            while (DB::table('slugs')
                ->where('type', $type)
                ->where('slug', $slug)
                ->when($existing, fn ($query) => $query->where('id', '!=', $existing->id))
                ->exists()) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }

            DB::table('slugs')->updateOrInsert(
                [
                    'type' => $type,
                    'sluggable_type' => $modelClass,
                    'sluggable_id' => $modelId,
                ],
                [
                    'slug' => $slug,
                    'updated_at' => now(),
                    'created_at' => $existing->created_at ?? now(),
                ]
            );
        };

        Article::withoutGlobalScopes()
            ->select(['id', 'title'])
            ->orderBy('id')
            ->chunkById(500, function ($articles) use ($ensureSlug) {
                foreach ($articles as $article) {
                    $ensureSlug('article', Article::class, $article->id, $article->title);
                }
            });

        Genre::query()
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(500, function ($genres) use ($ensureSlug) {
                foreach ($genres as $genre) {
                    $ensureSlug('genre', Genre::class, $genre->id, $genre->name);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('slugs');
    }
};
