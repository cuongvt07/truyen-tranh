<?php

namespace App\Http\Controllers\Client;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\Character;
use App\Models\Genre;
use App\Models\Tag;
use App\Scopes\ApprovedArticleScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyArticleController extends Controller
{
    /** Lấy truyện của chính mình (bỏ qua scope duyệt để thấy cả PENDING). */
    private function ownArticle($id): Article
    {
        $article = Article::withoutGlobalScope(ApprovedArticleScope::class)->findOrFail($id);
        abort_unless($article->user_id === Auth::id(), 403, 'Bạn không có quyền với truyện này.');
        return $article;
    }

    /** Danh sách truyện của tôi. */
    public function index()
    {
        $articles = Article::withoutGlobalScope(ApprovedArticleScope::class)
            ->where('user_id', Auth::id())
            ->withCount('chapters')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('client.my-articles.index', compact('articles'));
    }

    /** Form tạo truyện. */
    public function create()
    {
        return view('client.my-articles.form', [
            'article' => new Article(),
            'genres'  => Genre::orderBy('name')->get(),
            'myCharacters' => Character::where('user_id', Auth::id())->orderBy('name')->get(),
            'mode'    => 'create',
        ]);
    }

    /** Lưu truyện mới. */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->normalizeCreditFields($data);

        $article = new Article();
        $article->fill($data);
        $article->user_id = Auth::id();
        $article->status  = ArticleStatus::PENDING->value; // chờ admin duyệt trước khi public
        $article->cover_image = $this->resolveCover($request);
        $article->save();

        if ($bg = $this->resolveBackground($request)) {
            $article->background_image = $bg;
            $article->save();
        }
        $article->genres()->sync($request->input('genres', []));
        $this->syncAuthor($article, $request->input('author_name'));
        $this->syncTags($article, $request->input('tags'));
        $article->characters()->sync($request->input('characters', []));

        return redirect()->route('my-articles.index')->with('success', 'Đăng truyện thành công! Truyện đang chờ admin duyệt trước khi hiển thị công khai.');
    }

    /** Form sửa. */
    public function edit($id)
    {
        $article = $this->ownArticle($id);
        return view('client.my-articles.form', [
            'article' => $article,
            'genres'  => Genre::orderBy('name')->get(),
            'myCharacters' => Character::where('user_id', Auth::id())->orderBy('name')->get(),
            'mode'    => 'edit',
        ]);
    }

    /** Cập nhật. */
    public function update(Request $request, $id)
    {
        $article = $this->ownArticle($id);
        $data = $this->validateData($request);
        $data = $this->normalizeCreditFields($data);

        $article->fill($data);
        if ($request->hasFile('cover_image') || $request->filled('cover_image_url')) {
            $article->cover_image = $this->resolveCover($request);
        }
        if ($bg = $this->resolveBackground($request)) {
            $article->background_image = $bg;
        }
        $article->save();

        $article->genres()->sync($request->input('genres', []));
        $this->syncAuthor($article, $request->input('author_name'));
        $this->syncTags($article, $request->input('tags'));
        $article->characters()->sync($request->input('characters', []));

        return redirect()->route('my-articles.index')->with('success', 'Cập nhật truyện thành công!');
    }

    /** Xoá. */
    public function destroy($id)
    {
        $article = $this->ownArticle($id);
        $article->genres()->detach();
        $article->authors()->detach();
        $article->chapters()->delete();
        $article->delete();

        return redirect()->route('my-articles.index')->with('success', 'Đã xoá truyện.');
    }

    /** Form thêm chương. */
    public function createChapter($id)
    {
        $article = $this->ownArticle($id);
        $nextNumber = (int) $article->chapters()->max('number') + 1;
        return view('client.my-articles.chapter-form', compact('article', 'nextNumber'));
    }

    /** Lưu chương. */
    public function storeChapter(Request $request, $id)
    {
        $article = $this->ownArticle($id);
        $data = $request->validate([
            'number'      => ['required', 'integer', 'min:1'],
            'title'       => ['required', 'string', 'max:255'],
            'content'     => ['required', 'string'],
            'credit_cost' => ['nullable', 'integer', 'min:0'],
        ], [], ['number' => 'số chương', 'title' => 'tiêu đề', 'content' => 'nội dung']);

        Chapter::create([
            'article_id'  => $article->id,
            'number'      => $data['number'],
            'title'       => $data['title'],
            'content'     => $data['content'],
            'credit_cost' => $data['credit_cost'] !== '' ? ($data['credit_cost'] ?? null) : null,
        ]);
        $article->touch();

        return redirect()->route('my-articles.index')->with('success', 'Thêm chương thành công!');
    }

    /* ---------- helpers ---------- */

    private function normalizeCreditFields(array $data): array
    {
        if (array_key_exists('credit_start_chapter', $data) && $data['credit_start_chapter'] === '') {
            $data['credit_start_chapter'] = null;
        }
        if (array_key_exists('credit_per_chapter', $data) && $data['credit_per_chapter'] === '') {
            $data['credit_per_chapter'] = 0;
        }
        return $data;
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'alt_title'       => ['nullable', 'string', 'max:255'],
            'illustrator'     => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'novel_type'      => ['nullable', 'integer', 'in:0,1,2'],
            'country'         => ['nullable', 'integer'],
            'year_of_release' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'is_completed'    => ['nullable', 'boolean'],
            'is_adult'        => ['nullable', 'boolean'],
            'genres'          => ['nullable', 'array'],
            'genres.*'        => ['integer', 'exists:genres,id'],
            'characters'      => ['nullable', 'array'],
            'characters.*'    => ['integer', 'exists:characters,id'],
            'cover_image'     => ['nullable', 'image', 'max:4096'],
            'background'      => ['nullable', 'image', 'max:6144'],
            'author_name'         => ['nullable', 'string', 'max:255'],
            'tags'                => ['nullable', 'string', 'max:1000'],
            'credit_start_chapter' => ['nullable', 'integer', 'min:1'],
            'credit_per_chapter'   => ['nullable', 'integer', 'min:0'],
        ], [], [
            'title' => 'tên truyện', 'description' => 'mô tả', 'cover_image' => 'ảnh bìa',
        ]);
    }

    private function resolveBackground(Request $request): ?string
    {
        if ($request->hasFile('background')) {
            $img = $request->file('background');
            $name = time() . '-bg-' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $img->getClientOriginalName());
            $img->move(public_path('images/articles'), $name);
            return '/images/articles/' . $name;
        }
        return $request->filled('background_url') ? $request->input('background_url') : null;
    }

    private function syncTags(Article $article, ?string $raw): void
    {
        $names = collect(explode(',', (string) $raw))->map(fn ($t) => trim($t))->filter()->unique();
        $ids = $names->map(fn ($n) => \App\Models\Tag::firstOrCreate(['name' => $n])->id)->all();
        $article->tags()->sync($ids);
    }

    private function resolveCover(Request $request): ?string
    {
        if ($request->hasFile('cover_image')) {
            $img = $request->file('cover_image');
            $name = time() . '-' . preg_replace('/[^A-Za-z0-9.\-]/', '_', $img->getClientOriginalName());
            $img->move(public_path('images/articles'), $name);
            return '/images/articles/' . $name;
        }
        if ($request->filled('cover_image_url')) {
            return $request->input('cover_image_url');
        }
        return null;
    }

    private function syncAuthor(Article $article, ?string $name): void
    {
        $name = trim((string) $name);
        if ($name === '') {
            return;
        }
        $author = Author::firstOrCreate(['name' => $name]);
        $article->authors()->sync([$author->id]);
    }
}
