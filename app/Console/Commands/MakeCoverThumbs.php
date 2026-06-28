<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class MakeCoverThumbs extends Command
{
    protected $signature = 'covers:make-thumbs {--width=500} {--force}';
    protected $description = 'Tạo bản thu nhỏ -{width}.jpg cho cover các truyện đã có (backfill ảnh gốc cũ).';

    public function handle(): int
    {
        $w = max(1, (int) $this->option('width'));
        $force = (bool) $this->option('force');
        $made = 0; $skip = 0; $fail = 0;

        Article::withoutGlobalScopes()
            ->whereNotNull('cover_image')
            ->where('cover_image', 'like', '/storage/%')
            ->orderBy('id')
            ->chunkById(200, function ($articles) use (&$made, &$skip, &$fail, $w, $force) {
                foreach ($articles as $a) {
                    if (!$force && cover_thumb_url($a->cover_image, $w)) {
                        $skip++;
                        continue;
                    }
                    cover_make_thumb($a->cover_image, $w) ? $made++ : $fail++;
                }
            });

        $this->info("Cover thumbs -{$w}: tạo mới={$made}, đã có/bỏ qua={$skip}, lỗi={$fail}.");
        return self::SUCCESS;
    }
}
