<?php

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\Genre;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeoController extends Controller
{
    /** Số chương mỗi file sitemap con (giới hạn an toàn dưới 50k/file). */
    private const CHAPTERS_PER_PAGE = 10000;

    /** TTL cache mỗi mảnh sitemap (giờ). */
    private const TTL_HOURS = 6;

    /** robots.txt động. */
    public function robots()
    {
        $sitemap = url('/sitemap.xml');
        $extra = seo_setting('robots_txt_extra', '');

        $content = <<<TXT
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /login
Disallow: /register
Disallow: /search
Disallow: /api/
Disallow: /users/
Disallow: /dang-truyen
Disallow: /truyen-cua-toi

Allow: /images/
Allow: /static/
Allow: /media/

Sitemap: {$sitemap}

User-agent: MJ12bot
Disallow: /

User-agent: AhrefsBot
Crawl-delay: 10

{$extra}
TXT;

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** /sitemap.xml — sitemap index trỏ tới các sitemap con theo loại. */
    public function sitemap()
    {
        $xml = $this->cached('index', function () {
            $children = [
                route('seo.sitemap.pages'),
                route('seo.sitemap.genres'),
                route('seo.sitemap.authors'),
                route('seo.sitemap.articles'),
            ];
            $pages = max(1, (int) ceil($this->approvedChapterQuery()->count() / self::CHAPTERS_PER_PAGE));
            for ($p = 1; $p <= $pages; $p++) {
                $children[] = route('seo.sitemap.chapters', ['page' => $p]);
            }

            $now = now()->toAtomString();
            $out  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($children as $loc) {
                $out .= "  <sitemap>\n    <loc>" . htmlspecialchars($loc) . "</loc>\n    <lastmod>{$now}</lastmod>\n  </sitemap>\n";
            }
            $out .= '</sitemapindex>';
            return $out;
        });

        return $this->xml($xml);
    }

    /** /sitemap-pages.xml — trang chủ, listing & trang tĩnh. */
    public function sitemapPages()
    {
        $xml = $this->cached('pages', function () {
            $urls = [
                ['loc' => url('/'),                                  'freq' => 'daily',   'pri' => '1.0'],
                ['loc' => route('catalog.index'),                    'freq' => 'daily',   'pri' => '0.9'],
                ['loc' => route('home.show_new_update_articles'),    'freq' => 'daily',   'pri' => '0.8'],
                ['loc' => route('home.show_hot_articles'),           'freq' => 'daily',   'pri' => '0.8'],
                ['loc' => route('home.show_completed_articles'),     'freq' => 'weekly',  'pri' => '0.8'],
                ['loc' => route('pages.pricing'),                    'freq' => 'monthly', 'pri' => '0.6'],
                ['loc' => route('pages.faq'),                        'freq' => 'monthly', 'pri' => '0.4'],
                ['loc' => route('pages.rules'),                      'freq' => 'monthly', 'pri' => '0.4'],
                ['loc' => route('pages.dmca'),                       'freq' => 'monthly', 'pri' => '0.4'],
                ['loc' => route('pages.terms'),                      'freq' => 'monthly', 'pri' => '0.4'],
                ['loc' => route('pages.feedback'),                   'freq' => 'monthly', 'pri' => '0.4'],
            ];
            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /** /sitemap-genres.xml */
    public function sitemapGenres()
    {
        $xml = $this->cached('genres', function () {
            $urls = [];
            foreach (Genre::with('slug')->get(['id']) as $g) {
                $urls[] = ['loc' => route('genres.show', $g), 'freq' => 'daily', 'pri' => '0.7'];
            }
            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /** /sitemap-authors.xml */
    public function sitemapAuthors()
    {
        $xml = $this->cached('authors', function () {
            $urls = [];
            Author::orderBy('id')->chunk(1000, function ($authors) use (&$urls) {
                foreach ($authors as $a) {
                    $urls[] = ['loc' => route('authors.show', $a->id), 'freq' => 'weekly', 'pri' => '0.6'];
                }
            });
            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /** /sitemap-articles.xml — truyện đã duyệt. */
    public function sitemapArticles()
    {
        $xml = $this->cached('articles', function () {
            $urls = [];
            Article::where('status', ArticleStatus::APPROVED->value)
                ->orderByDesc('updated_at')
                ->select(['id', 'updated_at'])
                ->with('slug')
                ->chunk(1000, function ($articles) use (&$urls) {
                    foreach ($articles as $art) {
                        $urls[] = [
                            'loc'     => route('articles.show', $art),
                            'lastmod' => optional($art->updated_at)->toAtomString(),
                            'freq'    => 'weekly',
                            'pri'     => '0.8',
                        ];
                    }
                });
            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /** /sitemap-chapters-{page}.xml — chương của truyện đã duyệt (phân trang). */
    public function sitemapChapters(int $page = 1)
    {
        $page = max(1, $page);
        $xml = $this->cached("chapters_{$page}", function () use ($page) {
            $urls = [];
            $this->approvedChapterQuery()
                ->leftJoin('slugs', function ($join) {
                    $join->on('slugs.sluggable_id', '=', 'articles.id')
                        ->where('slugs.sluggable_type', Article::class)
                        ->where('slugs.type', 'article');
                })
                ->select(['chapters.article_id', 'chapters.number', 'chapters.updated_at', 'slugs.slug as article_slug'])
                ->orderBy('chapters.id')
                ->forPage($page, self::CHAPTERS_PER_PAGE)
                ->get()
                ->each(function ($ch) use (&$urls) {
                    $urls[] = [
                        'loc'     => route('articles.chapters.show', [$ch->article_slug ?? $ch->article_id, $ch->number]),
                        'lastmod' => optional($ch->updated_at)->toAtomString(),
                        'freq'    => 'monthly',
                        'pri'     => '0.6',
                    ];
                });
            return $this->urlset($urls);
        });

        return $this->xml($xml);
    }

    /** Query chương thuộc truyện đã duyệt. */
    private function approvedChapterQuery()
    {
        return Chapter::query()
            ->join('articles', 'articles.id', '=', 'chapters.article_id')
            ->where('articles.status', ArticleStatus::APPROVED->value);
    }

    /** Build <urlset> từ mảng url. */
    private function urlset(array $urls): string
    {
        $out  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $out .= "  <url>\n    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                $out .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $out .= "    <changefreq>{$u['freq']}</changefreq>\n    <priority>{$u['pri']}</priority>\n  </url>\n";
        }
        $out .= '</urlset>';
        return $out;
    }

    /** Cache theo version: tự mới khi nội dung đổi (bump_sitemap_version). */
    private function cached(string $key, callable $build): string
    {
        $v = (int) Cache::get('sitemap_version', 1);
        return Cache::remember("sitemap:{$key}:v{$v}", now()->addHours(self::TTL_HOURS), $build);
    }

    private function xml(string $content)
    {
        return response($content, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
