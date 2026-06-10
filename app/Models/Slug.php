<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Slug extends Model
{
    protected $fillable = [
        'type',
        'slug',
        'sluggable_type',
        'sluggable_id',
    ];

    public function sluggable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function normalize(string $source, ?string $fallback = null): string
    {
        $slug = Str::slug($source);

        return $slug !== '' ? $slug : Str::slug((string) $fallback);
    }

    public static function ensureFor(Model $model, string $type, string $source): self
    {
        $base = static::normalize($source);
        if ($base === '') {
            $base = $type . '-' . $model->getKey();
        }

        $existing = static::query()
            ->where('type', $type)
            ->where('sluggable_type', $model::class)
            ->where('sluggable_id', $model->getKey())
            ->first();

        if ($existing && Str::startsWith($existing->slug, $base)) {
            return $existing;
        }

        $slug = static::uniqueSlug($type, $base, $existing?->id);

        return static::updateOrCreate(
            [
                'type' => $type,
                'sluggable_type' => $model::class,
                'sluggable_id' => $model->getKey(),
            ],
            ['slug' => $slug]
        );
    }

    public static function uniqueSlug(string $type, string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('type', $type)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public static function resolve(string $type, string $slug)
    {
        return static::query()
            ->where('type', $type)
            ->where('slug', $slug)
            ->first()
            ?->sluggable;
    }
}
