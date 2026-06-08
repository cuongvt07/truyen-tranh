<?php

namespace App\Traits;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

trait HasSeo
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /** Lấy SEO đã resolve (custom override -> auto-generated fallback). */
    public function getSeo(): array
    {
        $m = $this->seoMeta;

        return [
            'title'       => $m?->title       ?: $this->generateSeoTitle(),
            'description' => $m?->description  ?: $this->generateSeoDescription(),
            'og_image'    => $m?->og_image     ?: $this->generateOgImage(),
            'canonical'   => $m?->canonical_url ?: $this->getCanonicalUrl(),
            'noindex'     => (bool) ($m?->noindex),
            'nofollow'    => (bool) ($m?->nofollow),
            'keyword'     => $m?->focus_keyword,
            'score'       => $m?->seo_score,
        ];
    }

    /** Lưu/ cập nhật SEO meta từ mảng input của form. */
    public function saveSeo(array $data): void
    {
        $clean = collect($data)->only([
            'title', 'description', 'canonical_url', 'og_image',
            'noindex', 'nofollow', 'focus_keyword', 'seo_score',
        ])->map(fn ($v) => $v === '' ? null : $v)->toArray();

        // Nếu tất cả rỗng -> xoá meta để dùng auto
        $hasAny = collect($clean)->filter(fn ($v) => !is_null($v) && $v !== false)->isNotEmpty();
        if (!$hasAny) {
            $this->seoMeta()->delete();
            return;
        }
        $clean['noindex']  = !empty($data['noindex']);
        $clean['nofollow'] = !empty($data['nofollow']);

        $this->seoMeta()->updateOrCreate([], $clean);
    }

    /* Mặc định — model override nếu muốn tuỳ biến. */
    protected function generateSeoTitle(): string
    {
        return (string) ($this->title ?? $this->name ?? '');
    }

    protected function generateSeoDescription(): string
    {
        $desc = strip_tags((string) ($this->description ?? ''));
        return Str::limit($desc, 155);
    }

    protected function generateOgImage(): string
    {
        return function_exists('novel_poster') ? novel_poster($this)
            : asset(ltrim(seo_setting('default_og_image', '/static/core/images/no_cover.webp'), '/'));
    }

    protected function getCanonicalUrl(): string
    {
        return url()->current();
    }
}
