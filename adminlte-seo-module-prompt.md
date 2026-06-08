# Prompt: Xây dựng Module SEO Toàn diện — Site Truyện Laravel (Blade SSR)
> Stack: Laravel + Blade | Render: Server-side | Mức bắt đầu: từ đầu

---

## TỔNG QUAN KIẾN TRÚC SEO

```
Site truyện có đặc thù SEO riêng:
- Hàng nghìn trang (truyện × chương × thể loại × tác giả)
- Nội dung chủ yếu là text dài → cơ hội ranking cao nếu cấu trúc đúng
- Nhiều trang trùng lặp tiềm năng (chương 1 của 1000 truyện có cấu trúc giống nhau)
- Cần crawl budget tối ưu cho bot Google
- Structured Data (Schema.org) đặc thù cho nội dung sách/truyện

Module SEO cần xây dựng theo 7 tầng:
  Tầng 1: Meta tags cơ bản (title, description, canonical)
  Tầng 2: Open Graph + Twitter Card
  Tầng 3: Structured Data / Schema.org
  Tầng 4: Sitemap XML động
  Tầng 5: Robots.txt thông minh
  Tầng 6: Performance (Core Web Vitals)
  Tầng 7: SEO Admin Panel (quản lý trong AdminLTE)
```

---

## PHẦN 1 — CÀI ĐẶT & CẤU TRÚC FILE

### 1.1 Package cần dùng

```bash
# Package SEO chính
composer require artesaos/seotools

# Sitemap động
composer require spatie/laravel-sitemap

# Schema.org structured data
composer require spatie/schema-org

# Image optimization
composer require spatie/laravel-image-optimizer
npm install imagemin imagemin-webp

# Cache (dùng built-in Laravel)
# Không cần package thêm nếu dùng Redis hoặc file cache
```

### 1.2 Cấu trúc file cần tạo

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/SeoController.php       ← CRUD cấu hình SEO
│   └── Middleware/
│       └── SeoMiddleware.php              ← Inject SEO vào mọi response
├── Models/
│   └── SeoMeta.php                        ← Model lưu SEO per-page
├── Services/
│   └── SeoService.php                     ← Logic tạo meta tags
│   └── SchemaService.php                  ← Tạo Structured Data
│   └── SitemapService.php                 ← Tạo sitemap động
├── Console/Commands/
│   └── GenerateSitemap.php               ← Command tạo sitemap
│   └── SeoAudit.php                      ← Command kiểm tra SEO

config/
└── seo.php                                ← Cấu hình SEO toàn site

database/migrations/
└── create_seo_metas_table.php

resources/views/
├── layouts/
│   └── _seo.blade.php                     ← Partial chứa toàn bộ meta tags
└── admin/seo/
    ├── index.blade.php                    ← Danh sách SEO settings
    ├── global.blade.php                   ← Cài đặt SEO toàn site
    └── analyzer.blade.php                 ← Công cụ phân tích SEO
```

---

## PHẦN 2 — DATABASE

### 2.1 Migration bảng seo_metas

```php
// database/migrations/xxxx_create_seo_metas_table.php
Schema::create('seo_metas', function (Blueprint $table) {
    $table->id();

    // Liên kết polymorphic — gắn với bất kỳ model nào
    $table->morphs('seoable'); // seoable_type + seoable_id
    // VD: seoable_type = 'App\Models\Story', seoable_id = 1

    // Meta cơ bản
    $table->string('title', 70)->nullable();          // SEO title (max 60 ký tự)
    $table->text('description')->nullable();           // Meta description (max 160 ký tự)
    $table->string('canonical_url')->nullable();       // Canonical URL

    // Open Graph
    $table->string('og_title', 100)->nullable();
    $table->text('og_description')->nullable();
    $table->string('og_image')->nullable();            // URL ảnh OG (1200×630)
    $table->string('og_type')->default('website');    // website | article | book

    // Twitter Card
    $table->string('twitter_card')->default('summary_large_image');
    $table->string('twitter_title', 100)->nullable();
    $table->text('twitter_description')->nullable();
    $table->string('twitter_image')->nullable();

    // Indexing
    $table->boolean('noindex')->default(false);        // true = không index
    $table->boolean('nofollow')->default(false);       // true = không follow links
    $table->string('robots')->nullable();              // Custom robots directive

    // Schema.org
    $table->string('schema_type')->nullable();         // Book | WebPage | BreadcrumbList
    $table->json('schema_data')->nullable();           // JSON-LD data thêm

    // Focus keyword (để tính điểm SEO)
    $table->string('focus_keyword')->nullable();
    $table->tinyInteger('seo_score')->nullable();      // 0-100

    // Timestamps
    $table->timestamps();

    // Index quan trọng
    $table->index(['seoable_type', 'seoable_id']);
});

// Bảng cấu hình SEO toàn site
Schema::create('seo_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('group')->default('general'); // general | social | advanced
    $table->timestamps();
});
```

### 2.2 Seeder cài đặt mặc định

```php
// database/seeders/SeoSettingsSeeder.php
$settings = [
    // General
    ['key' => 'site_name',           'value' => 'TênSiteTruyện',           'group' => 'general'],
    ['key' => 'title_separator',     'value' => ' | ',                      'group' => 'general'],
    ['key' => 'title_format',        'value' => '{page_title}{sep}{site}',  'group' => 'general'],
    ['key' => 'default_description', 'value' => 'Đọc truyện online...',    'group' => 'general'],
    ['key' => 'default_og_image',    'value' => '/images/og-default.jpg',  'group' => 'general'],

    // Social
    ['key' => 'facebook_page_url',   'value' => '',  'group' => 'social'],
    ['key' => 'twitter_username',    'value' => '',  'group' => 'social'],
    ['key' => 'facebook_app_id',     'value' => '',  'group' => 'social'],

    // Advanced
    ['key' => 'google_analytics_id', 'value' => '',  'group' => 'advanced'],
    ['key' => 'google_tag_manager',  'value' => '',  'group' => 'advanced'],
    ['key' => 'google_site_verify',  'value' => '',  'group' => 'advanced'],
    ['key' => 'bing_site_verify',    'value' => '',  'group' => 'advanced'],
    ['key' => 'robots_txt_extra',    'value' => '',  'group' => 'advanced'],
];
```

---

## PHẦN 3 — MODEL & SERVICE

### 3.1 Trait HasSeo (gắn vào mọi model)

```php
// app/Traits/HasSeo.php
trait HasSeo
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    // Lấy SEO meta, fallback về auto-generated nếu chưa có
    public function getSeo(): array
    {
        $meta = $this->seoMeta;

        return [
            'title'       => $meta?->title       ?: $this->generateSeoTitle(),
            'description' => $meta?->description  ?: $this->generateSeoDescription(),
            'og_image'    => $meta?->og_image     ?: $this->generateOgImage(),
            'canonical'   => $meta?->canonical_url ?: $this->getCanonicalUrl(),
            'noindex'     => $meta?->noindex      ?? false,
            'nofollow'    => $meta?->nofollow     ?? false,
        ];
    }

    // Mỗi model tự implement các method này
    abstract protected function generateSeoTitle(): string;
    abstract protected function generateSeoDescription(): string;
    abstract protected function getCanonicalUrl(): string;

    protected function generateOgImage(): string
    {
        return $this->cover
            ? asset($this->cover)
            : asset(setting('default_og_image'));
    }
}
```

### 3.2 Implement trong Model Story

```php
// app/Models/Story.php
class Story extends Model
{
    use HasSeo;

    protected function generateSeoTitle(): string
    {
        // Format: "Tên Truyện - Đọc Online | TênSite"
        return $this->name . ' - Đọc Truyện Online';
    }

    protected function generateSeoDescription(): string
    {
        // Lấy 160 ký tự đầu của mô tả, bỏ HTML tags
        $desc = strip_tags($this->description ?? '');
        return Str::limit($desc, 155, '...')
            ?: "Đọc truyện {$this->name} - {$this->author->name}. "
             . "{$this->chapters_count} chương, cập nhật mới nhất.";
    }

    protected function getCanonicalUrl(): string
    {
        return route('story.show', $this->slug);
    }
}
```

### 3.3 Implement trong Model Chapter

```php
// app/Models/Chapter.php
class Chapter extends Model
{
    use HasSeo;

    protected function generateSeoTitle(): string
    {
        return "Chương {$this->number}: {$this->title} - {$this->story->name}";
    }

    protected function generateSeoDescription(): string
    {
        // Lấy đoạn đầu nội dung chương
        $content = strip_tags($this->content ?? '');
        $preview = Str::limit($content, 140);
        return $preview ?: "Đọc chương {$this->number} truyện {$this->story->name} online.";
    }

    protected function getCanonicalUrl(): string
    {
        return route('chapter.show', [$this->story->slug, $this->number]);
    }
}
```

### 3.4 SeoService — Trái tim của module

```php
// app/Services/SeoService.php
class SeoService
{
    public function __construct(
        private SEOMeta $seoMeta,
        private OpenGraph $openGraph,
        private TwitterCard $twitterCard,
    ) {}

    // Áp dụng SEO cho trang Truyện
    public function forStory(Story $story): void
    {
        $seo = $story->getSeo();
        $siteName = setting('site_name');
        $sep = setting('title_separator', ' | ');

        // Title
        $this->seoMeta->setTitle($seo['title'] . $sep . $siteName);
        $this->seoMeta->setDescription($seo['description']);
        $this->seoMeta->setCanonical($seo['canonical']);

        // Robots
        if ($seo['noindex']) {
            $this->seoMeta->addMeta('robots', 'noindex,nofollow');
        }

        // Open Graph
        $this->openGraph
            ->setType('book')
            ->setTitle($seo['title'])
            ->setDescription($seo['description'])
            ->setUrl($seo['canonical'])
            ->addImage($seo['og_image'], ['width' => 1200, 'height' => 630])
            ->addProperty('book:author', $story->author->name)
            ->addProperty('book:tag', $story->tags->pluck('name')->join(','))
            ->addProperty('article:published_time', $story->created_at->toIso8601String())
            ->addProperty('article:modified_time', $story->updated_at->toIso8601String());

        // Twitter Card
        $this->twitterCard
            ->setType('summary_large_image')
            ->setTitle($seo['title'])
            ->setDescription($seo['description'])
            ->setImage($seo['og_image']);

        // Breadcrumb data
        session(['breadcrumbs' => [
            ['name' => 'Trang chủ',   'url' => route('home')],
            ['name' => $story->category->name ?? 'Thể loại', 'url' => route('category.show', $story->category->slug ?? '')],
            ['name' => $story->name,  'url' => $seo['canonical']],
        ]]);
    }

    // Áp dụng SEO cho trang Chương
    public function forChapter(Chapter $chapter): void
    {
        $seo = $chapter->getSeo();
        $story = $chapter->story;
        $siteName = setting('site_name');

        $this->seoMeta->setTitle($seo['title'] . ' | ' . $siteName);
        $this->seoMeta->setDescription($seo['description']);
        $this->seoMeta->setCanonical($seo['canonical']);

        // Prev/Next links — RẤT quan trọng cho chương truyện
        if ($chapter->prev) {
            $this->seoMeta->addMeta('', '', [], [
                'rel'  => 'prev',
                'href' => route('chapter.show', [$story->slug, $chapter->prev->number])
            ], 'link');
        }
        if ($chapter->next) {
            $this->seoMeta->addMeta('', '', [], [
                'rel'  => 'next',
                'href' => route('chapter.show', [$story->slug, $chapter->next->number])
            ], 'link');
        }

        // Chương thường noindex nếu là bản trả phí hoặc draft
        if ($chapter->is_paid || $chapter->status !== 'active') {
            $this->seoMeta->addMeta('robots', 'noindex,follow');
        }

        $this->openGraph
            ->setType('article')
            ->setTitle($seo['title'])
            ->setDescription($seo['description'])
            ->setUrl($seo['canonical'])
            ->addImage($seo['og_image']);
    }

    // Áp dụng SEO cho trang Thể loại / Tag
    public function forCategory(Category $category, int $page = 1): void
    {
        $siteName = setting('site_name');
        $suffix = $page > 1 ? " - Trang {$page}" : '';

        $title = "Truyện {$category->name}{$suffix} | {$siteName}";
        $desc  = $category->description
            ?: "Đọc truyện {$category->name} hay nhất, cập nhật mới nhất tại {$siteName}.";

        $this->seoMeta->setTitle($title);
        $this->seoMeta->setDescription(Str::limit($desc, 155));
        $this->seoMeta->setCanonical(route('category.show', $category->slug));

        // Trang 2+ của danh mục: canonical về trang 1 hoặc noindex
        // Chọn 1 trong 2 chiến lược:
        // Chiến lược A (recommended): rel=canonical về trang 1
        if ($page > 1) {
            $this->seoMeta->setCanonical(route('category.show', $category->slug));
        }
        // Chiến lược B: noindex trang 2+
        // if ($page > 1) $this->seoMeta->addMeta('robots', 'noindex,follow');

        $this->openGraph
            ->setType('website')
            ->setTitle($title)
            ->setDescription($desc);
    }

    // Trang tìm kiếm — luôn noindex
    public function forSearch(string $query): void
    {
        $this->seoMeta->setTitle("Tìm kiếm: {$query} | " . setting('site_name'));
        $this->seoMeta->addMeta('robots', 'noindex,follow');
        $this->seoMeta->setCanonical(request()->url());
    }
}
```

---

## PHẦN 4 — BLADE TEMPLATE SEO

### 4.1 Partial `_seo.blade.php` (chèn vào `<head>`)

```blade
{{-- resources/views/layouts/_seo.blade.php --}}

{{-- 1. BASIC META --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

{!! SEOMeta::generate() !!}

{{-- 2. CANONICAL (luôn có) --}}
@if(SEOMeta::getCanonical())
<link rel="canonical" href="{{ SEOMeta::getCanonical() }}">
@endif

{{-- 3. OPEN GRAPH --}}
{!! OpenGraph::generate() !!}

{{-- 4. TWITTER CARD --}}
{!! TwitterCard::generate() !!}

{{-- 5. VERIFICATION TAGS --}}
@if(setting('google_site_verify'))
<meta name="google-site-verification" content="{{ setting('google_site_verify') }}">
@endif
@if(setting('bing_site_verify'))
<meta name="msvalidate.01" content="{{ setting('bing_site_verify') }}">
@endif

{{-- 6. STRUCTURED DATA / SCHEMA.ORG --}}
@stack('schema')

{{-- 7. BREADCRUMB SCHEMA (tự động từ session) --}}
@if(session('breadcrumbs'))
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    @foreach(session('breadcrumbs') as $i => $crumb)
    {
      "@type": "ListItem",
      "position": {{ $i + 1 }},
      "name": "{{ $crumb['name'] }}",
      "item": "{{ $crumb['url'] }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endif

{{-- 8. WEBSITE SCHEMA (chỉ trang chủ) --}}
@if(request()->is('/'))
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ setting('site_name') }}",
  "url": "{{ config('app.url') }}",
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "{{ route('search') }}?q={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
</script>
@endif

{{-- 9. GOOGLE ANALYTICS / GTM --}}
@if(setting('google_tag_manager') && app()->environment('production'))
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ setting('google_tag_manager') }}');</script>
@endif

{{-- 10. PRECONNECT (performance) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@stack('preconnect')

{{-- 11. FAVICON --}}
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<meta name="theme-color" content="#1e2a3a">
```

### 4.2 Schema cho trang Truyện

```blade
{{-- Chèn vào view story/show.blade.php --}}
@push('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Book",
  "name": "{{ $story->name }}",
  "url": "{{ route('story.show', $story->slug) }}",
  "description": "{{ Str::limit(strip_tags($story->description), 200) }}",
  "image": "{{ $story->cover ? asset($story->cover) : asset('images/no-cover.jpg') }}",
  "author": {
    "@type": "Person",
    "name": "{{ $story->author->name ?? 'Đang cập nhật' }}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{ setting('site_name') }}",
    "url": "{{ config('app.url') }}"
  },
  "genre": [
    @foreach($story->categories as $cat)
    "{{ $cat->name }}"{{ !$loop->last ? ',' : '' }}
    @endforeach
  ],
  "numberOfPages": {{ $story->chapters_count ?? 0 }},
  "inLanguage": "vi",
  "datePublished": "{{ $story->created_at->toIso8601String() }}",
  "dateModified": "{{ $story->updated_at->toIso8601String() }}",
  @if($story->rating_avg)
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ number_format($story->rating_avg, 1) }}",
    "ratingCount": "{{ $story->rating_count }}",
    "bestRating": "5",
    "worstRating": "1"
  },
  @endif
  "workExample": [
    @foreach($story->chapters->take(3) as $chap)
    {
      "@type": "Chapter",
      "name": "Chương {{ $chap->number }}: {{ $chap->title }}",
      "url": "{{ route('chapter.show', [$story->slug, $chap->number]) }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endpush
```

### 4.3 Schema cho trang Chương

```blade
{{-- Chèn vào view chapter/show.blade.php --}}
@push('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Chapter",
  "name": "Chương {{ $chapter->number }}: {{ $chapter->title }}",
  "url": "{{ route('chapter.show', [$story->slug, $chapter->number]) }}",
  "isPartOf": {
    "@type": "Book",
    "name": "{{ $story->name }}",
    "url": "{{ route('story.show', $story->slug) }}"
  },
  "author": {
    "@type": "Person",
    "name": "{{ $story->author->name ?? 'Đang cập nhật' }}"
  },
  "datePublished": "{{ $chapter->created_at->toIso8601String() }}",
  "dateModified": "{{ $chapter->updated_at->toIso8601String() }}",
  "inLanguage": "vi",
  "wordCount": {{ $chapter->word_count ?? 0 }}
}
</script>
@endpush
```

---

## PHẦN 5 — SITEMAP ĐỘNG

### 5.1 Command tạo Sitemap

```php
// app/Console/Commands/GenerateSitemap.php
class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate {--type=all : all|stories|chapters|categories}';
    protected $description = 'Generate XML sitemap for the novel site';

    public function handle(SitemapService $service): void
    {
        $type = $this->option('type');

        match($type) {
            'stories'    => $service->generateStoriesSitemap(),
            'chapters'   => $service->generateChaptersSitemap(),
            'categories' => $service->generateCategoriesSitemap(),
            default      => $service->generateAll(),
        };

        $this->info("✅ Sitemap generated successfully.");
    }
}
```

### 5.2 SitemapService

```php
// app/Services/SitemapService.php
class SitemapService
{
    public function generateAll(): void
    {
        // Sitemap index — trỏ tới các sitemap con
        $sitemapIndex = SitemapIndex::create();

        $this->generateStoriesSitemap();
        $this->generateChaptersSitemap();
        $this->generateCategoriesSitemap();
        $this->generateStaticSitemap();

        $sitemapIndex
            ->add(Url::create('/sitemap-stories.xml')->setLastModificationDate(now()))
            ->add(Url::create('/sitemap-chapters.xml')->setLastModificationDate(now()))
            ->add(Url::create('/sitemap-categories.xml')->setLastModificationDate(now()))
            ->add(Url::create('/sitemap-static.xml')->setLastModificationDate(now()))
            ->writeToFile(public_path('sitemap.xml'));
    }

    public function generateStoriesSitemap(): void
    {
        $sitemap = Sitemap::create();

        // Chỉ lấy truyện đang active, eager load cần thiết
        Story::where('status', 'active')
            ->select(['id', 'slug', 'cover', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->chunk(500, function ($stories) use ($sitemap) {
                foreach ($stories as $story) {
                    $url = Url::create(route('story.show', $story->slug))
                        ->setLastModificationDate($story->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8);

                    // Thêm ảnh bìa vào sitemap nếu có
                    if ($story->cover) {
                        $url->addImage(
                            asset($story->cover),
                            $story->name
                        );
                    }

                    $sitemap->add($url);
                }
            });

        $sitemap->writeToFile(public_path('sitemap-stories.xml'));
    }

    public function generateChaptersSitemap(): void
    {
        $sitemap = Sitemap::create();

        // Chỉ index chương miễn phí, đang active
        Chapter::where('status', 'active')
            ->where('is_paid', false)
            ->select(['id', 'story_id', 'number', 'updated_at'])
            ->with('story:id,slug')
            ->orderBy('updated_at', 'desc')
            ->chunk(1000, function ($chapters) use ($sitemap) {
                foreach ($chapters as $chapter) {
                    $sitemap->add(
                        Url::create(route('chapter.show', [$chapter->story->slug, $chapter->number]))
                            ->setLastModificationDate($chapter->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.6)
                    );
                }
            });

        $sitemap->writeToFile(public_path('sitemap-chapters.xml'));
    }

    public function generateCategoriesSitemap(): void
    {
        $sitemap = Sitemap::create();

        // Thể loại — priority cao vì là trang hub
        Category::where('status', 'active')
            ->get()
            ->each(function ($cat) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('category.show', $cat->slug))
                        ->setLastModificationDate($cat->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.9)
                );
            });

        // Author pages
        Author::where('status', 'active')
            ->get()
            ->each(function ($author) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('author.show', $author->slug))
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7)
                );
            });

        $sitemap->writeToFile(public_path('sitemap-categories.xml'));
    }

    public function generateStaticSitemap(): void
    {
        $sitemap = Sitemap::create();

        // Các trang tĩnh
        $staticPages = [
            ['url' => route('home'),        'priority' => 1.0, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => route('story.list'),  'priority' => 0.9, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => route('ranking'),     'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => route('completed'),   'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
        ];

        foreach ($staticPages as $page) {
            $sitemap->add(
                Url::create($page['url'])
                    ->setChangeFrequency($page['freq'])
                    ->setPriority($page['priority'])
            );
        }

        $sitemap->writeToFile(public_path('sitemap-static.xml'));
    }
}
```

### 5.3 Auto-regenerate Sitemap

```php
// app/Console/Kernel.php — Lên lịch tự động
protected function schedule(Schedule $schedule): void
{
    // Sitemap thể loại/trang tĩnh: mỗi 6 giờ
    $schedule->command('sitemap:generate --type=categories')->everySixHours();

    // Sitemap truyện: mỗi giờ (khi có truyện mới)
    $schedule->command('sitemap:generate --type=stories')->hourly();

    // Sitemap chương: mỗi 30 phút (update liên tục)
    $schedule->command('sitemap:generate --type=chapters')->everyThirtyMinutes();
}

// Hoặc trigger khi có data mới (Observer pattern):
// app/Observers/ChapterObserver.php
class ChapterObserver
{
    public function created(Chapter $chapter): void
    {
        // Chạy job tạo lại sitemap chapters sau 5 phút
        GenerateSitemapJob::dispatch('chapters')->delay(now()->addMinutes(5));
    }
}
```

---

## PHẦN 6 — ROBOTS.TXT THÔNG MINH

### 6.1 Route trả về robots.txt động

```php
// routes/web.php
Route::get('/robots.txt', [SeoController::class, 'robotsTxt'])->name('robots');

// app/Http/Controllers/SeoController.php
public function robotsTxt(): Response
{
    $content = view('seo.robots')->render();
    return response($content, 200)->header('Content-Type', 'text/plain');
}
```

### 6.2 Template robots.txt

```blade
{{-- resources/views/seo/robots.blade.php --}}
User-agent: *
Allow: /

# Không index trang không có giá trị SEO
Disallow: /admin/
Disallow: /login
Disallow: /register
Disallow: /password/
Disallow: /search?*
Disallow: /api/
Disallow: /?sort=*
Disallow: /?page=*&sort=*
Disallow: /user/
Disallow: /notification/

# Cho phép ảnh bìa (quan trọng cho image search)
Allow: /storage/covers/
Allow: /images/

# Sitemap
Sitemap: {{ config('app.url') }}/sitemap.xml

# Crawl delay cho bot nhẹ
User-agent: AhrefsBot
Crawl-delay: 10

User-agent: SemrushBot
Crawl-delay: 10

User-agent: MJ12bot
Disallow: /

# Custom rules từ admin
{{ setting('robots_txt_extra') }}
```

---

## PHẦN 7 — URL STRUCTURE CHUẨN SEO

### 7.1 Route design

```php
// routes/web.php — URL structure tối ưu SEO

// Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Truyện — URL ngắn, không có /story/ prefix
Route::get('/{slug}', [StoryController::class, 'show'])->name('story.show');
// VD: /toan-chuc-phap-su → tốt hơn /truyen/toan-chuc-phap-su

// Chương — rõ cấu trúc phân cấp
Route::get('/{slug}/chuong-{number}', [ChapterController::class, 'show'])->name('chapter.show');
// VD: /toan-chuc-phap-su/chuong-1

// Thể loại
Route::get('/the-loai/{slug}', [CategoryController::class, 'show'])->name('category.show');
// VD: /the-loai/tien-hiep

// Tác giả
Route::get('/tac-gia/{slug}', [AuthorController::class, 'show'])->name('author.show');

// Tìm kiếm — noindex
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');

// Danh sách, bảng xếp hạng
Route::get('/danh-sach-truyen', [StoryController::class, 'index'])->name('story.list');
Route::get('/bang-xep-hang', [RankingController::class, 'index'])->name('ranking');
Route::get('/truyen-hoan-thanh', [StoryController::class, 'completed'])->name('completed');
Route::get('/truyen-moi-cap-nhat', [StoryController::class, 'updated'])->name('updated');
```

### 7.2 Slug generation chuẩn

```php
// app/Helpers/SlugHelper.php
function generateSlug(string $text): string
{
    // Xử lý tiếng Việt → ASCII
    $text = mb_strtolower($text);

    $map = [
        'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
        'ă'=>'a','ắ'=>'a','ặ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
        'đ'=>'d',
        'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
        'ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
        'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
        'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
        'ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o',
        'ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
        'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
        'ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
        'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
    ];

    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));

    return $text;
}
```

---

## PHẦN 8 — PERFORMANCE (CORE WEB VITALS)

### 8.1 Cấu hình Nginx (khi deploy)

```nginx
# /etc/nginx/sites-available/truyen.conf

server {
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/javascript application/javascript
               application/json application/xml image/svg+xml;

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|webp|css|js|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        add_header Vary "Accept-Encoding";
    }

    # Security headers (cũng tốt cho SEO signals)
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Cache sitemap
    location = /sitemap.xml {
        expires 1h;
        add_header Cache-Control "public";
    }
}
```

### 8.2 Laravel Cache cho trang nặng

```php
// Trong Controller — cache trang danh mục
public function show(Category $category): View
{
    $cacheKey = "category_{$category->slug}_page_" . request('page', 1);

    $stories = Cache::remember($cacheKey, now()->addHours(2), function () use ($category) {
        return $category->stories()
            ->active()
            ->with(['author', 'latestChapter'])
            ->withCount('chapters')
            ->orderBy('updated_at', 'desc')
            ->paginate(24);
    });

    app(SeoService::class)->forCategory($category, request('page', 1));

    return view('category.show', compact('category', 'stories'));
}

// Xóa cache khi có truyện mới trong danh mục
Story::created(function ($story) {
    Cache::tags(["category_{$story->category_id}"])->flush();
});
```

### 8.3 Lazy load ảnh bìa (LCP optimization)

```blade
{{-- Ảnh đầu tiên trên trang: KHÔNG lazy load (quan trọng cho LCP) --}}
@if($loop->first)
<img src="{{ asset($story->cover) }}"
     alt="{{ $story->name }} - đọc truyện online"
     width="150" height="200"
     fetchpriority="high">
@else
{{-- Ảnh còn lại: lazy load --}}
<img src="{{ asset('images/placeholder.webp') }}"
     data-src="{{ asset($story->cover) }}"
     alt="{{ $story->name }}"
     width="150" height="200"
     loading="lazy"
     class="lazy-img">
@endif
```

---

## PHẦN 9 — SEO ADMIN PANEL (AdminLTE)

### 9.1 Menu Admin SEO

```
Sidebar menu SEO gồm:
  📊 SEO
  ├── Tổng quan SEO          ← Dashboard điểm SEO toàn site
  ├── Cài đặt chung          ← Site name, default OG, verify tags
  ├── Truyện cần tối ưu      ← Danh sách truyện chưa có SEO meta
  ├── Sitemap                ← Xem + regenerate sitemap
  ├── Robots.txt             ← Chỉnh sửa robots.txt
  └── Kiểm tra URL           ← Nhập URL để xem SEO preview
```

### 9.2 Trang Cài đặt SEO chung

```
Card "Thông tin cơ bản":
  - Tên website (dùng trong title)
  - Dấu phân cách title (|, -, –, »)
  - Format title: {page_title} {sep} {site_name}
  - Mô tả mặc định (khi trang chưa có description riêng)
  - Ảnh OG mặc định (upload, 1200×630px, hiện preview)

Card "Xác minh tìm kiếm":
  - Google Search Console verification code
  - Bing Webmaster verification code

Card "Mạng xã hội":
  - Facebook Page URL
  - Facebook App ID
  - Twitter/X username (@)
  - OG Locale (vi_VN)

Card "Công cụ phân tích":
  - Google Analytics 4 ID (G-XXXXXX)
  - Google Tag Manager ID (GTM-XXXXX)

Card "Nâng cao":
  - Thêm robots.txt tùy chỉnh (textarea)
  - Heder X-Robots-Tag
  - Canonical domain (www vs non-www)
```

### 9.3 SEO Meta Box (tích hợp trong form Truyện/Chương)

```blade
{{-- Thêm card này vào cột phải của form Truyện và form Chương --}}
<div class="card card-secondary card-outline collapsed-card" id="seo-card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-search mr-2"></i>SEO
      <span class="badge badge-pill ml-2" id="seo-score-badge">--</span>
    </h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>
  <div class="card-body">

    {{-- Focus Keyword --}}
    <div class="form-group">
      <label>Từ khóa trọng tâm</label>
      <input type="text" name="seo[focus_keyword]" class="form-control form-control-sm"
             id="focus-keyword" placeholder="VD: đọc truyện tiên hiệp"
             value="{{ old('seo.focus_keyword', $item->seoMeta->focus_keyword ?? '') }}">
      <small class="text-muted">Từ khóa chính bạn muốn xếp hạng</small>
    </div>

    {{-- SEO Title --}}
    <div class="form-group">
      <label>SEO Title</label>
      <input type="text" name="seo[title]" class="form-control form-control-sm"
             id="seo-title" maxlength="70"
             placeholder="Để trống = tự tạo từ tên"
             value="{{ old('seo.title', $item->seoMeta->title ?? '') }}">
      <div class="d-flex justify-content-between">
        <small class="text-muted">Tối ưu: 50–60 ký tự</small>
        <small><span id="seo-title-count">0</span>/70</small>
      </div>
      {{-- Preview Google --}}
      <div class="seo-preview mt-2 p-2 border rounded bg-white" style="font-size:13px">
        <div style="color:#1a0dab;font-size:18px" id="preview-title">--</div>
        <div style="color:#006621;font-size:13px">{{ config('app.url') }}/ten-truyen</div>
        <div style="color:#545454" id="preview-desc">--</div>
      </div>
    </div>

    {{-- SEO Description --}}
    <div class="form-group">
      <label>Meta Description</label>
      <textarea name="seo[description]" class="form-control form-control-sm"
                id="seo-description" rows="3" maxlength="160"
                placeholder="Để trống = tự tạo từ nội dung">{{ old('seo.description', $item->seoMeta->description ?? '') }}</textarea>
      <div class="d-flex justify-content-between">
        <small class="text-muted">Tối ưu: 130–155 ký tự</small>
        <small><span id="seo-desc-count">0</span>/160</small>
      </div>
    </div>

    {{-- Robots --}}
    <div class="form-group">
      <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="noindex" name="seo[noindex]" value="1"
               {{ old('seo.noindex', $item->seoMeta->noindex ?? 0) ? 'checked' : '' }}>
        <label class="custom-control-label" for="noindex">
          Không index trang này (noindex)
        </label>
      </div>
    </div>

    {{-- SEO Score --}}
    <div id="seo-analysis" class="mt-3">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <small class="font-weight-bold">Điểm SEO</small>
        <span class="badge badge-pill badge-secondary" id="seo-score-text">Chưa phân tích</span>
      </div>
      <div class="progress mb-2" style="height:8px">
        <div class="progress-bar" id="seo-score-bar" style="width:0%"></div>
      </div>
      <ul class="list-unstyled mb-0" id="seo-checklist" style="font-size:12px"></ul>
    </div>
  </div>
</div>

<script>
// Real-time SEO analyzer
(function() {
  const titleEl = document.getElementById('seo-title');
  const descEl = document.getElementById('seo-description');
  const keywordEl = document.getElementById('focus-keyword');

  function analyze() {
    const title = titleEl.value || document.getElementById('name')?.value || '';
    const desc = descEl.value;
    const keyword = keywordEl.value.toLowerCase();
    const checks = [];
    let score = 0;

    // Title checks
    if (title.length >= 30 && title.length <= 60) { score += 15; checks.push({ok:true, msg:'Title độ dài tốt (30-60 ký tự)'}); }
    else checks.push({ok:false, msg:`Title ${title.length < 30 ? 'quá ngắn' : 'quá dài'} (hiện: ${title.length} ký tự)`});

    if (keyword && title.toLowerCase().includes(keyword)) { score += 20; checks.push({ok:true, msg:'Từ khóa có trong Title'}); }
    else if (keyword) checks.push({ok:false, msg:'Từ khóa CHƯA có trong Title'});

    // Description checks
    if (desc.length >= 120 && desc.length <= 155) { score += 15; checks.push({ok:true, msg:'Description độ dài tốt'}); }
    else if (desc.length > 0) checks.push({ok:false, msg:`Description ${desc.length < 120 ? 'quá ngắn' : 'quá dài'}`});
    else checks.push({ok:false, msg:'Chưa có Meta Description'});

    if (keyword && desc.toLowerCase().includes(keyword)) { score += 10; checks.push({ok:true, msg:'Từ khóa có trong Description'}); }
    else if (keyword) checks.push({ok:false, msg:'Từ khóa chưa có trong Description'});

    // Render
    const bar = document.getElementById('seo-score-bar');
    const scoreText = document.getElementById('seo-score-text');
    const scoreColor = score >= 70 ? 'success' : score >= 40 ? 'warning' : 'danger';
    bar.style.width = score + '%';
    bar.className = `progress-bar bg-${scoreColor}`;
    scoreText.textContent = score + '/100';
    scoreText.className = `badge badge-pill badge-${scoreColor}`;

    const checklist = document.getElementById('seo-checklist');
    checklist.innerHTML = checks.map(c =>
      `<li><i class="fas fa-${c.ok ? 'check text-success' : 'times text-danger'} mr-1"></i>${c.msg}</li>`
    ).join('');

    // Update Google preview
    document.getElementById('preview-title').textContent = title || '--';
    document.getElementById('preview-desc').textContent = desc || '--';
  }

  [titleEl, descEl, keywordEl].forEach(el => el?.addEventListener('input', analyze));
  analyze();
})();
</script>
```

---

## PHẦN 10 — CHECKLIST SEO KHI LAUNCH

```
PRE-LAUNCH:
  [ ] Cài đặt SSL (HTTPS) — bắt buộc
  [ ] Redirect www → non-www (hoặc ngược lại) nhất quán
  [ ] Không có trang 404 hàng loạt
  [ ] Tất cả ảnh có alt text có nghĩa
  [ ] Không có broken link nội bộ
  [ ] Sitemap đã generate và accessible tại /sitemap.xml
  [ ] Robots.txt đúng, không block Googlebot nhầm
  [ ] Google Search Console: submit sitemap
  [ ] Google Analytics 4: verify tracking

ON-GOING:
  [ ] Mỗi truyện mới: nhập focus keyword + description
  [ ] Mỗi tháng: kiểm tra Core Web Vitals trong Search Console
  [ ] Theo dõi Crawl Coverage — trang nào bị excluded
  [ ] Monitor 404 errors và redirect kịp thời
  [ ] Kiểm tra Index Coverage báo cáo hàng tuần

CONTENT SEO:
  [ ] Title truyện: chứa từ khóa + thể loại (VD: "Đọc Truyện Tiên Hiệp - Tên Truyện")
  [ ] Description: 130-155 ký tự, có call-to-action ("Đọc ngay", "Cập nhật chương mới")
  [ ] Tên thể loại tối ưu (VD: "Tiên Hiệp" thay vì "xianxia")
  [ ] Internal linking: mỗi trang truyện link đến thể loại, tác giả
  [ ] Breadcrumb hiển thị đúng trên mọi trang
  [ ] Tốc độ tải trang < 3 giây (kiểm tra PageSpeed Insights)
```
