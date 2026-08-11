<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\StoreArticleRequest;
use App\Http\Requests\Admin\Article\UpdateArticleRequest;
use App\Http\Requests\Article\ChangeStatusRequest;
use App\Models\Article;
use App\Models\Author;
use App\Models\Character;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
class ArticleController extends Controller
{
    use HandlesImageUploads;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        // Bỏ scope duyệt để admin/poster thấy cả bài Chờ duyệt / Đã ẩn
        if ($currentUser->is_admin) {
            $articles = Article::withoutGlobalScope(\App\Scopes\ApprovedArticleScope::class);
        } else {
            // else currentUser is Poster
            $articles = $currentUser->articles()->withoutGlobalScope(\App\Scopes\ApprovedArticleScope::class);
        }
        // Eager load để tránh N+1 (authors, genres) + đếm chương (tính cả chương hẹn giờ).
        $articles->with(['authors:id,name', 'genres:id,name', 'user:id,name'])
                 ->withCount([
                     'chapters' => fn ($q) => $q->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class),
                     'bookmarks', // số người "Quan tâm" (add to list) -> ưu tiên dịch
                 ]);

        if ($search = trim((string) $request->input('search'))) {
            $articles->where('title', 'like', '%' . $search . '%');
        }
        if ($request->filled('status')) {
            $articles->where('status', (int) $request->input('status'));
        }
        if ($request->filled('completed')) {
            $articles->where('is_completed', (int) $request->input('completed'));
        }
        // Nguồn: truyện do user tự gửi vs admin tạo
        if ($request->input('source') === 'user') {
            $articles->where('is_user_submitted', true);
        } elseif ($request->input('source') === 'admin') {
            $articles->where('is_user_submitted', false);
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'views':
                $articles->orderByDesc('view');
                break;
            case 'updated':
                $articles->orderByDesc('updated_at');
                break;
            case 'title':
                $articles->orderBy('title');
                break;
            case 'interest':
                $articles->orderByDesc('bookmarks_count');
                break;
            default:
                $articles->orderByDesc('id');
                break;
        }

        $articles = $articles->paginate($request->input('per_page', 20))->withQueryString();
        return view('admin.articles.index', ['articles' => $articles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $article = new Article();
        $authors = Author::all();
        $genres = Genre::all();
        $countries = Country::orderBy('sort_order')->get();
        $articleOptions = Article::orderBy('title')->get(['id', 'title']);
        return view('admin.articles.create', [
            'article' => $article,
            'authors' => $authors,
            'genres' => $genres,
            'countries' => $countries,
            'articleOptions' => $articleOptions,
            'selectedGenres' => array(),
            'selectedAuthors' => array(),
            'characters' => Character::orderBy('name')->get(),
            'selectedCharacterIds' => array(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArticleRequest $request)
    {
        $request->validated();
        $validateData = $request->except(['user_id']);
        $validateData['user_id'] = Auth::id();
        $validateData = $this->normalizeDetailBlockSettings($request, $validateData);
        $validateData = $this->normalizeCreditFields($validateData);

        $validateData = $this->uploadCoverImage($request, $validateData);
        // Admin bat "Auto duyet" trong Cai dat -> truyen public ngay.
        // Khong co dong nay thi status roi ve mac dinh cua DB (0 = cho duyet).
        if (setting('auto_approve_articles') === '1' && !isset($validateData['status'])) {
            $validateData['status'] = \App\Enums\ArticleStatus::APPROVED->value;
        }

        $article = Article::create($validateData);
        Slug::ensureFor($article, 'article', $request->input('slug') ?: $article->title);
        $article->genres()->attach($validateData['genres'] ?? []);
        $article->authors()->attach($validateData['authors'] ?? []);
        $article->characters()->sync($request->input('characters', []));
        $this->syncTags($article, $request->input('tags'));

        if ($request->has('affiliate_links')) {
            foreach ($request->affiliate_links as $index => $linkData) {
                $affiliateLink = new \App\Models\AffiliateLink();
                $affiliateLink->article_id = $article->id;
                $affiliateLink->link = $linkData['link'];

                // Nếu có file
                if (isset($linkData['image_file']) && $request->file("affiliate_links.$index.image_file")) {
                    $file = $request->file("affiliate_links.$index.image_file");
                    $affiliateLink->image_path = $this->storePublicImage($file, 'images/articles/affiliates');
                } else {
                    $affiliateLink->image_path = '/images/articles/default.jpg';
                }

                $affiliateLink->save();
            }
        }

        return redirect()->route('admin.articles.index')->with('success', __('messages.flash.article.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        $this->authorizeArticle($article);
        $article->load('slug');
        $authors = Author::all();
        $genres = Genre::all();
        $countries = Country::orderBy('sort_order')->get();
        $articleOptions = Article::where('id', '!=', $article->id)->orderBy('title')->get(['id', 'title']);
        $selectedGenres = $article->genres->pluck('id')->toArray();
        $selectedAuthors = $article->authors->pluck('id')->toArray();
        return view('admin.articles.edit', [
            'article' => $article,
            'authors' => $authors,
            'genres' => $genres,
            'countries' => $countries,
            'articleOptions' => $articleOptions,
            'selectedGenres' => $selectedGenres,
            'selectedAuthors' => $selectedAuthors,
            'characters' => Character::orderBy('name')->get(),
            'selectedCharacterIds' => $article->characters->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $this->authorizeArticle($article);
        $request->validated();
        // Ownership is derived from the authenticated user and must never be writable from the form.
        $data = $request->except(['user_id']);
        $data = $this->normalizeDetailBlockSettings($request, $data, $article->id);
        $data = $this->normalizeCreditFields($data);

        $data = $this->uploadCoverImage($request, $data);

        $article->update($data);
        Slug::ensureFor($article, 'article', $request->input('slug') ?: $article->title);
        $article->genres()->sync($data['genres'] ?? []);
        $article->authors()->sync($data['authors'] ?? []);
        $article->characters()->sync($request->input('characters', []));
        $this->syncTags($article, $request->input('tags'));

        $article->affiliateLinks()->delete();

        if ($request->has('affiliate_links')) {
            foreach ($request->affiliate_links as $index => $linkData) {
                $affiliateLink = new \App\Models\AffiliateLink();
                $affiliateLink->article_id = $article->id;
                $affiliateLink->link = $linkData['link'];

                if (isset($linkData['image_file']) && $request->file("affiliate_links.$index.image_file")) {
                    $file = $request->file("affiliate_links.$index.image_file");
                    $affiliateLink->image_path = $this->storePublicImage($file, 'images/articles/affiliates');
                } else {
                    $affiliateLink->image_path = '/images/articles/default.jpg';
                }

                $affiliateLink->save();
            }
        }

        return redirect()->route('admin.articles.index')->with('success', __('messages.flash.article.updated'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $this->authorizeArticle($article);
        $article->delete();
        return redirect()->route('admin.articles.index')
            ->with('success', __('messages.flash.article.deleted'));
    }

    public function updateStatus(
        Article $article,
        $status
    ) {
        $this->authorizeArticle($article);
        if (!validateArticleStatus($status)) {
            return redirect()->route('admin.articles.index');
        }
        if ((int) $article->status !== 2) {
            $statusText = mb_strtolower(ArticleStatus::from($status)->label());
        } else {
            $statusText = "đã được hiển thị lại";
        }

        $message = 'Truyện "'.$article->title.'" '.$statusText.'!';

        $article->status = $status;
        $article->save();
        return redirect()->route('admin.articles.index')
            ->with('success', $message);
    }

    public function updateCompleteStatus(Article $article)
    {
        $this->authorizeArticle($article);
        $article->is_completed = !$article->is_completed;
        $article->save();
        if ($article->is_completed) {
            $message = 'Thay đổi trạng thái thành đã hoàn thành thành công!';
        } else {
            $message = 'Thay đổi trạng thái thành chưa hoàn thành thành công!';
        }
        return redirect()->route('admin.articles.index')
            ->with('success', $message);
    }

    /** Đồng bộ tags (text phân cách dấu phẩy -> firstOrCreate). */
    private function syncTags(Article $article, ?string $raw): void
    {
        $names = collect(explode(',', (string) $raw))
            ->map(function ($tag) {
                return trim($tag);
            })
            ->filter()
            ->unique();
        $ids = $names->map(function ($name) {
            return \App\Models\Tag::firstOrCreate(['name' => $name])->id;
        })->all();
        $article->tags()->sync($ids);
    }

    private function normalizeDetailBlockSettings(Request $request, array $data, ?int $articleId = null): array
    {
        $data['similar_article_ids'] = $this->normalizeIdList(
            $request->input('similar_article_ids', []),
            $articleId
        );
        $data['translation_request_article_ids'] = $this->normalizeIdList(
            $request->input('translation_request_article_ids', []),
            $articleId
        );
        $data['related_genre_ids'] = $this->normalizeIdList(
            $request->input('related_genre_ids', [])
        );

        return $data;
    }

    private function normalizeIdList($ids, ?int $excludeId = null): ?array
    {
        $ids = collect((array) $ids)
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) use ($excludeId) {
                return $id > 0 && ($excludeId === null || $id !== $excludeId);
            })
            ->unique()
            ->values()
            ->all();

        return $ids ?: null;
    }

    /**
     * @param  FormRequest $request
     * @param  array  $validateData
     *
     * @return array
     */
    private function uploadCoverImage(
        FormRequest $request,
        array $validateData
    ): array {
        if ($request->hasFile('cover_image')) {
            $validateData['cover_image'] = $this->storePublicImage($request->file('cover_image'), 'images/articles');
            // Tạo sẵn bản thu nhỏ -500.jpg để dùng cho card trang chủ / trang con (nhẹ hơn ảnh gốc).
            cover_make_thumb($validateData['cover_image'], 500);
        } else {
            if ($validateData['cover_image_url']) {
                $validateData['cover_image'] = $validateData['cover_image_url'];
            } else {
                $validateData['cover_image'] = '/images/articles/default.jpg';
            }
        }
        return $validateData;
    }

    /**
     * Upload affiliate image if exists, otherwise use default or provided URL.
     *
     * @param  UpdateArticleRequest  $request
     * @param  array  $data
     *
     * @return array
     */
    private function normalizeCreditFields(array $data): array
    {
        if (isset($data['credit_start_chapter']) && $data['credit_start_chapter'] === '') {
            $data['credit_start_chapter'] = null;
        }
        if (isset($data['credit_per_chapter']) && $data['credit_per_chapter'] === '') {
            $data['credit_per_chapter'] = 0;
        }
        if (isset($data['team_id']) && $data['team_id'] === '') {
            $data['team_id'] = null;
        }
        return $data;
    }

    private function authorizeArticle(Article $article): void
    {
        $user = Auth::user();
        abort_unless($user->is_admin || $article->user_id === $user->id, 403);
    }

    private function uploadAffiImage(UpdateArticleRequest $request, array $data): array
    {
        if ($request->hasFile('affi_image')) {
            $data['affi_image'] = $this->storePublicImage($request->file('affi_image'), 'images/articles/affiliates');
        } elseif (!empty($data['affi_image_url'])) {
            $data['affi_image'] = $data['affi_image_url'];
        } else {
            $data['affi_image'] = '/images/articles/default.jpg';
        }

        return $data;
    }

}
