<?php

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class ImportNovelightLocal extends Command
{
    protected $signature = 'novelight:import-local
        {path? : Local HTTrack mirror root}
        {--limit=30 : Number of novels to import}
        {--chapters-min=5 : Skip novels with fewer chapters than this}
        {--chapters-max=10 : Maximum chapters per novel}
        {--user-id= : User ID that owns imported novels}
        {--fresh : Clean current story data before importing}
        {--dry-run : Parse the mirror and report candidates without changing DB/files}
        {--live-ajax : Try Novelight live ajax when local chapter body is missing}';

    protected $description = 'Clean/import sample Novelight stories from a local HTTrack HTML mirror.';

    private string $rootPath;

    public function handle(): int
    {
        $this->rootPath = rtrim($this->argument('path') ?: 'C:\\My Web Sites\\truyentranh\\novelight.net', "\\/");
        $limit = max(1, (int) $this->option('limit'));
        $chaptersMin = max(1, (int) $this->option('chapters-min'));
        $chaptersMax = max($chaptersMin, (int) $this->option('chapters-max'));

        if (!is_dir($this->rootPath)) {
            $this->error("Mirror path does not exist: {$this->rootPath}");
            return self::FAILURE;
        }

        $bookFiles = $this->bookFiles();
        if ($this->option('dry-run')) {
            return $this->dryRun($bookFiles, $limit, $chaptersMin, $chaptersMax);
        }

        $userId = $this->resolveUserId();

        if ($this->option('fresh')) {
            $this->cleanStoryData();
        }

        $imported = 0;
        $skipped = 0;
        $placeholderChapters = 0;

        foreach ($bookFiles as $bookFile) {
            if ($imported >= $limit) {
                break;
            }

            try {
                $book = $this->parseBook($bookFile);
                if (!$book['title']) {
                    $skipped++;
                    continue;
                }

                $chapters = $this->selectedChapters($book['chapters'], $chaptersMin, $chaptersMax);
                if (count($chapters) < $chaptersMin) {
                    $this->warn("Skip {$book['title']}: only ".count($chapters).' chapters found.');
                    $skipped++;
                    continue;
                }

                DB::transaction(function () use ($book, $chapters, $userId, &$placeholderChapters): void {
                    $article = Article::unguarded(fn () => Article::withoutGlobalScopes()->create([
                        'title' => $this->uniqueArticleTitle($book['title']),
                        'description' => $book['description'] ?: 'Updating',
                        'is_completed' => $book['is_completed'],
                        'cover_image' => $book['cover_image'] ?: '/static/core/images/no_cover.webp',
                        'background_image' => $book['background_image'],
                        'view' => random_int(120, 9000),
                        'status' => ArticleStatus::APPROVED->value,
                        'user_id' => $userId,
                        'novel_type' => 0,
                        'rating' => $book['rating'],
                        'rating_count' => $book['rating_count'],
                    ]));

                    $this->syncAuthors($article, $book['authors']);
                    $this->syncGenres($article, $book['genres']);
                    $this->syncTags($article, $book['tags']);

                    foreach (array_values($chapters) as $index => $chapterData) {
                        $content = $this->chapterContent($chapterData);
                        if ($content['is_placeholder']) {
                            $placeholderChapters++;
                        }

                        Chapter::unguarded(fn () => Chapter::create([
                            'article_id' => $article->id,
                            'number' => $index + 1,
                            'title' => $chapterData['title'] ?: 'Chapter '.($index + 1),
                            'content' => $content['text'],
                            'view' => random_int(20, 1200),
                        ]));
                    }
                });

                $imported++;
                $this->info("Imported {$imported}/{$limit}: {$book['title']} (".count($chapters).' chapters)');
            } catch (Throwable $e) {
                $skipped++;
                $this->warn("Skip {$bookFile}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Done. Imported {$imported} novels, skipped {$skipped}. Placeholder chapters: {$placeholderChapters}.");

        if ($placeholderChapters > 0 && !$this->option('live-ajax')) {
            $this->line('Tip: rerun with --live-ajax if you want to try fetching missing chapter text from Novelight.');
        }

        return self::SUCCESS;
    }

    /**
     * @param array<int, string> $bookFiles
     */
    private function dryRun(array $bookFiles, int $limit, int $chaptersMin, int $chaptersMax): int
    {
        $accepted = 0;
        $skipped = 0;

        $this->info("Scanning {$this->rootPath}");

        foreach ($bookFiles as $bookFile) {
            if ($accepted >= $limit) {
                break;
            }

            try {
                $book = $this->parseBook($bookFile);
                $chapters = $this->selectedChapters($book['chapters'], $chaptersMin, $chaptersMax);

                if (!$book['title'] || count($chapters) < $chaptersMin) {
                    $skipped++;
                    continue;
                }

                $accepted++;
                $this->line(sprintf(
                    '%02d. %s | chapters: %d | genres: %s',
                    $accepted,
                    $book['title'],
                    count($chapters),
                    implode(', ', array_slice($book['genres'], 0, 4)) ?: 'none'
                ));
            } catch (Throwable $e) {
                $skipped++;
                $this->warn("Skip {$bookFile}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Dry run done. Ready to import {$accepted} novels, skipped {$skipped}.");

        return self::SUCCESS;
    }

    private function resolveUserId(): int
    {
        $requested = $this->option('user-id');
        if ($requested && User::query()->whereKey((int) $requested)->exists()) {
            return (int) $requested;
        }

        $userId = User::query()->orderBy('id')->value('id');
        if (!$userId) {
            throw new \RuntimeException('No users found. Seed/create a user before importing articles.');
        }

        return (int) $userId;
    }

    private function cleanStoryData(): void
    {
        $this->warn('Cleaning story data...');

        if (Schema::hasTable('seo_metas')) {
            DB::table('seo_metas')
                ->whereIn('seoable_type', [Article::class, Chapter::class])
                ->delete();
        }

        $tables = [
            'reading_histories',
            'comment_reports',
            'comment_votes',
            'comments',
            'bookmarks',
            'collection_article',
            'article_character',
            'article_tag',
            'articles_authors',
            'articles_genres',
            'affiliate_links',
            'chapters',
            'articles',
            'authors',
            'genres',
            'tags',
        ];

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                DB::table($table)->truncate();
            }
        } finally {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function bookFiles(): array
    {
        $files = glob($this->rootPath.DIRECTORY_SEPARATOR.'book'.DIRECTORY_SEPARATOR.'*.html') ?: [];
        sort($files, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values(array_filter($files, fn (string $file): bool => is_file($file)));
    }

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     cover_image: ?string,
     *     background_image: ?string,
     *     is_completed: bool,
     *     rating: float,
     *     rating_count: int,
     *     authors: array<int, string>,
     *     genres: array<int, string>,
     *     tags: array<int, string>,
     *     chapters: array<int, array{href: string, file: ?string, source_id: ?string, source_number: ?float, title: string}>
     * }
     */
    private function parseBook(string $bookFile): array
    {
        [$dom, $xpath] = $this->loadHtml($bookFile);

        $title = $this->text($xpath, "//header[contains(@class,'header-manga')]//h1");
        $description = $this->paragraphText($xpath, "//*[@id='information']//section[contains(concat(' ', normalize-space(@class), ' '), ' text-info ')][1]");
        $tags = $this->nodeTexts($xpath, "//*[@id='information']//section[contains(concat(' ', normalize-space(@class), ' '), ' tags ')]//a");
        $genres = $this->nodeTexts($xpath, "//div[contains(concat(' ', normalize-space(@class), ' '), ' second-information ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' sub-header ') and normalize-space()='Genres']/following-sibling::*[contains(concat(' ', normalize-space(@class), ' '), ' info ')][1]//a");
        $status = $this->text($xpath, "(//div[contains(concat(' ', normalize-space(@class), ' '), ' second-information ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' sub-header ') and normalize-space()='Status']/following-sibling::*[contains(concat(' ', normalize-space(@class), ' '), ' info ')][1])[1]");
        $cover = $this->attr($xpath, "(//div[contains(concat(' ', normalize-space(@class), ' '), ' second-information ')]//div[contains(concat(' ', normalize-space(@class), ' '), ' poster ')]//img)[1]", 'src');
        $background = $this->extractBackground(file_get_contents($bookFile) ?: '');
        $authors = $this->nodeTexts($xpath, "//span[contains(concat(' ', normalize-space(@class), ' '), ' author ')]");

        $chapterNodes = $xpath->query("//*[@id='all-chapters-list']//a[contains(concat(' ', normalize-space(@class), ' '), ' chapter ')]");
        if (!$chapterNodes || $chapterNodes->length === 0) {
            $chapterNodes = $xpath->query("//a[contains(concat(' ', normalize-space(@class), ' '), ' chapter ')]");
        }

        $chapters = [];
        foreach ($chapterNodes ?: [] as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $href = trim($node->getAttribute('href'));
            if ($href === '') {
                continue;
            }

            $rawTitle = $this->normalizeText($node->textContent);
            $sourceNumber = $this->parseChapterNumber($rawTitle);
            $sourceId = $this->sourceChapterId($href);

            $chapters[] = [
                'href' => $href,
                'file' => $this->resolveChapterFile($bookFile, $href),
                'source_id' => $sourceId,
                'source_number' => $sourceNumber,
                'title' => $this->chapterTitle($rawTitle),
            ];
        }

        return [
            'title' => $title,
            'description' => $description,
            'cover_image' => $this->localizeAsset($cover, $bookFile, 'covers'),
            'background_image' => $this->localizeAsset($background, $bookFile, 'backgrounds'),
            'is_completed' => str_contains(Str::lower($status), 'completed'),
            'rating' => 0.0,
            'rating_count' => 0,
            'authors' => $this->uniqueClean($authors ?: ['NoveLight']),
            'genres' => $this->uniqueClean($genres ?: $this->genresFromKeywords($dom)),
            'tags' => $this->uniqueClean($tags),
            'chapters' => $this->uniqueChapters($chapters),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $chapters
     * @return array<int, array<string, mixed>>
     */
    private function selectedChapters(array $chapters, int $min, int $max): array
    {
        usort($chapters, function (array $a, array $b): int {
            $left = $a['source_number'] ?? PHP_INT_MAX;
            $right = $b['source_number'] ?? PHP_INT_MAX;

            if ($left === $right) {
                return strcmp((string) $a['href'], (string) $b['href']);
            }

            return $left <=> $right;
        });

        if (count($chapters) < $min) {
            return $chapters;
        }

        return array_slice($chapters, 0, min($max, count($chapters)));
    }

    /**
     * @param array{file: ?string, source_id: ?string, href: string} $chapter
     * @return array{text: string, is_placeholder: bool}
     */
    private function chapterContent(array $chapter): array
    {
        $text = null;

        if (!empty($chapter['file']) && is_file($chapter['file'])) {
            [$dom, $xpath] = $this->loadHtml($chapter['file']);
            $text = $this->paragraphText($xpath, "//*[contains(concat(' ', normalize-space(@class), ' '), ' chapter-text ')]");
        }

        if (!$text && $this->option('live-ajax') && !empty($chapter['source_id'])) {
            $text = $this->liveChapterContent($chapter['source_id']);
        }

        if ($text) {
            return ['text' => $text, 'is_placeholder' => false];
        }

        $source = $chapter['source_id'] ? "Novelight chapter {$chapter['source_id']}" : $chapter['href'];

        return [
            'text' => "Noi dung chuong chua co trong ban HTML offline.\n\nSource: {$source}",
            'is_placeholder' => true,
        ];
    }

    private function liveChapterContent(string $chapterId): ?string
    {
        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->get("https://novelight.net/book/ajax/read-chapter/{$chapterId}");

            if (!$response->ok()) {
                return null;
            }

            $content = (string) ($response->json('content') ?? '');
            if ($content === '') {
                return null;
            }

            return $this->htmlToText($content);
        } catch (Throwable) {
            return null;
        }
    }

    private function syncAuthors(Article $article, array $names): void
    {
        $ids = [];
        foreach ($this->uniqueClean($names ?: ['NoveLight']) as $name) {
            $ids[] = Author::query()->firstOrCreate(['name' => Str::limit($name, 100, '')])->id;
        }

        $article->authors()->sync($ids);
    }

    private function syncGenres(Article $article, array $names): void
    {
        $ids = [];
        foreach ($this->uniqueClean($names ?: ['Web Novel']) as $name) {
            $ids[] = Genre::query()->firstOrCreate(['name' => Str::limit($name, 255, '')])->id;
        }

        $article->genres()->sync($ids);
    }

    private function syncTags(Article $article, array $names): void
    {
        if (!Schema::hasTable('tags') || !$names) {
            return;
        }

        $ids = [];
        foreach ($this->uniqueClean($names) as $name) {
            $ids[] = Tag::query()->firstOrCreate(['name' => Str::limit($name, 255, '')])->id;
        }

        $article->tags()->sync($ids);
    }

    private function uniqueArticleTitle(string $title): string
    {
        $base = Str::limit($title, 240, '');
        $candidate = $base;
        $counter = 2;

        while (Article::withoutGlobalScopes()->where('title', $candidate)->exists()) {
            $candidate = Str::limit($base, 235, '')." ({$counter})";
            $counter++;
        }

        return $candidate;
    }

    /**
     * @return array{DOMDocument, DOMXPath}
     */
    private function loadHtml(string $file): array
    {
        $html = file_get_contents($file);
        if ($html === false) {
            throw new \RuntimeException("Cannot read {$file}");
        }

        return $this->loadHtmlString($html);
    }

    /**
     * @return array{DOMDocument, DOMXPath}
     */
    private function loadHtmlString(string $html): array
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return [$dom, new DOMXPath($dom)];
    }

    private function text(DOMXPath $xpath, string $query, ?DOMNode $context = null): string
    {
        $nodes = $xpath->query($query, $context);
        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        return $this->normalizeText($nodes->item(0)?->textContent ?? '');
    }

    private function attr(DOMXPath $xpath, string $query, string $attribute): ?string
    {
        $nodes = $xpath->query($query);
        $node = $nodes && $nodes->length > 0 ? $nodes->item(0) : null;

        if (!$node instanceof DOMElement) {
            return null;
        }

        $value = trim($node->getAttribute($attribute));

        return $value !== '' ? $value : null;
    }

    /**
     * @return array<int, string>
     */
    private function nodeTexts(DOMXPath $xpath, string $query): array
    {
        $values = [];
        $nodes = $xpath->query($query);
        foreach ($nodes ?: [] as $node) {
            $text = $this->normalizeText($node->textContent ?? '');
            if ($text !== '') {
                $values[] = $text;
            }
        }

        return $values;
    }

    private function paragraphText(DOMXPath $xpath, string $query): string
    {
        $nodes = $xpath->query($query);
        if (!$nodes || $nodes->length === 0) {
            return '';
        }

        $root = $nodes->item(0);
        $paragraphs = [];
        $paragraphNodes = $xpath->query('.//p|.//div', $root);

        foreach ($paragraphNodes ?: [] as $node) {
            $text = $this->normalizeText($node->textContent ?? '');
            if ($text !== '' && !in_array($text, $paragraphs, true)) {
                $paragraphs[] = $text;
            }
        }

        if (!$paragraphs) {
            $paragraphs[] = $this->normalizeText($root?->textContent ?? '');
        }

        return trim(implode("\n\n", array_filter($paragraphs)));
    }

    private function htmlToText(string $html): string
    {
        [$dom, $xpath] = $this->loadHtmlString($html);

        return $this->paragraphText($xpath, '/*');
    }

    private function extractBackground(string $html): ?string
    {
        if (preg_match('/page-panel[^>]+background-image:\s*url\(([^)]+)\)/i', $html, $matches)) {
            return trim($matches[1], " \t\n\r\0\x0B'\"");
        }

        return null;
    }

    private function localizeAsset(?string $src, string $sourceFile, string $subdir): ?string
    {
        if (!$src) {
            return null;
        }

        $cleanSrc = preg_replace('/[?#].*$/', '', $src) ?: $src;
        $localPath = null;

        if (str_starts_with($cleanSrc, 'http://') || str_starts_with($cleanSrc, 'https://')) {
            $path = parse_url($cleanSrc, PHP_URL_PATH);
            if ($path) {
                $localPath = $this->rootPath.str_replace('/', DIRECTORY_SEPARATOR, $path);
            }
        } else {
            $localPath = dirname($sourceFile).DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cleanSrc);
        }

        $realPath = $localPath ? realpath($localPath) : false;
        if (!$realPath || !is_file($realPath)) {
            return str_starts_with($src, 'http') ? $src : null;
        }

        if ($this->option('dry-run')) {
            return $src;
        }

        $extension = pathinfo($realPath, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = Str::slug(pathinfo($realPath, PATHINFO_FILENAME));
        $filename = ($filename ?: sha1($realPath)).'-'.substr(sha1($realPath), 0, 10).'.'.$extension;
        $relative = "images/articles/novelight/{$subdir}/{$filename}";
        $destination = public_path($relative);

        File::ensureDirectoryExists(dirname($destination));
        if (!File::exists($destination)) {
            File::copy($realPath, $destination);
        }

        return '/'.str_replace('\\', '/', $relative);
    }

    private function resolveChapterFile(string $bookFile, string $href): ?string
    {
        $cleanHref = preg_replace('/[?#].*$/', '', trim($href)) ?: $href;

        if (preg_match('~(?:/|^)book/chapter/(\d+)~', $cleanHref, $matches)) {
            $file = $this->rootPath.DIRECTORY_SEPARATOR.'book'.DIRECTORY_SEPARATOR.'chapter'.DIRECTORY_SEPARATOR.$matches[1].'.html';
            return is_file($file) ? $file : null;
        }

        $file = dirname($bookFile).DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cleanHref);
        $realPath = realpath($file);

        return $realPath && is_file($realPath) ? $realPath : null;
    }

    private function sourceChapterId(string $href): ?string
    {
        if (preg_match('~(?:/|^)chapter/(\d+)(?:\.html)?$~', preg_replace('/[?#].*$/', '', $href) ?: $href, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function parseChapterNumber(string $text): ?float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*chapter/i', $text, $matches)) {
            return (float) $matches[1];
        }

        if (preg_match('/chapter\s*(\d+(?:\.\d+)?)/i', $text, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    private function chapterTitle(string $text): string
    {
        $title = preg_replace('/^\s*\d+(?:\.\d+)?\s*chapter\s*-\s*/i', '', $text) ?: $text;
        $title = preg_replace('/\s*(?:Chapter written by|Give thanks|Bookmark).*$/i', '', $title) ?: $title;

        return Str::limit($this->normalizeText($title), 255, '');
    }

    /**
     * @param array<int, array<string, mixed>> $chapters
     * @return array<int, array<string, mixed>>
     */
    private function uniqueChapters(array $chapters): array
    {
        $seen = [];
        $unique = [];

        foreach ($chapters as $chapter) {
            $key = $chapter['source_id'] ?: $chapter['href'];
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $chapter;
        }

        return $unique;
    }

    /**
     * @return array<int, string>
     */
    private function genresFromKeywords(DOMDocument $dom): array
    {
        $metas = $dom->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            if (!$meta instanceof DOMElement || Str::lower($meta->getAttribute('name')) !== 'keywords') {
                continue;
            }

            $keywords = array_map('trim', explode(',', $meta->getAttribute('content')));

            return array_values(array_filter($keywords, fn (string $value): bool => $value !== '' && Str::lower($value) !== 'web novel'));
        }

        return ['Web Novel'];
    }

    /**
     * @param array<int, string> $values
     * @return array<int, string>
     */
    private function uniqueClean(array $values): array
    {
        $result = [];

        foreach ($values as $value) {
            $clean = $this->normalizeText($value);
            if ($clean === '') {
                continue;
            }

            $key = Str::lower($clean);
            $result[$key] = $clean;
        }

        return array_values($result);
    }

    private function normalizeText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\xc2\xa0", "\r"], [' ', "\n"], $text);
        $lines = preg_split('/\n+/', $text) ?: [];
        $lines = array_map(fn (string $line): string => trim(preg_replace('/[ \t]+/u', ' ', $line) ?: ''), $lines);

        return trim(implode("\n", array_filter($lines, fn (string $line): bool => $line !== '')));
    }
}
