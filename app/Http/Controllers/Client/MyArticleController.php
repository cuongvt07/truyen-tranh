<?php

namespace App\Http\Controllers\Client;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Author;
use App\Models\Chapter;
use App\Models\Character;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Tag;
use App\Models\Team;
use App\Scopes\ApprovedArticleScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MyArticleController extends Controller
{
    use HandlesImageUploads;

    /** Lấy truyện của chính mình (bỏ qua scope duyệt để thấy cả PENDING). */
    private function ownArticle($id): Article
    {
        $article = Article::withoutGlobalScope(ApprovedArticleScope::class)->with('team')->findOrFail($id);
        abort_unless($this->canManageArticle($article), 403, 'You do not have permission to manage this story.');
        return $article;
    }

    /** Danh sách truyện của tôi. */
    public function index()
    {
        $teamIds = $this->manageableTeamIds();

        $articles = Article::withoutGlobalScope(ApprovedArticleScope::class)
            ->where(function ($q) use ($teamIds) {
                $q->where('user_id', Auth::id());

                if ($teamIds->isNotEmpty()) {
                    $q->orWhereIn('team_id', $teamIds);
                }
            })
            ->with('team:id,name')
            ->withCount('chapters')
            ->orderByDesc('updated_at')
            ->paginate(20);

        $articles->getCollection()->each(function (Article $article) {
            $article->can_delete = $this->canDeleteArticle($article);
        });

        return view('client.my-articles.index', compact('articles'));
    }

    /** Form tạo truyện. */
    public function create()
    {
        $this->ensurePurchased();
        return view('client.my-articles.form', [
            'article'      => new Article(),
            'genres'       => Genre::orderBy('name')->get(),
            'countries'    => Country::orderBy('sort_order')->get(),
            'myCharacters' => Character::orderBy('name')->get(), // nhân vật dùng chung toàn site
            'myTeams'      => $this->selectableTeams(),
            'mode'         => 'create',
        ]);
    }

    /** Lưu truyện mới. */
    public function store(Request $request)
    {
        $this->ensurePurchased();
        $data = $this->validateData($request);
        $data = $this->normalizeCreditFields($data);

        $article = new Article();
        $article->fill($data);
        $article->user_id = Auth::id();
        $article->is_user_submitted = true;                // truyện do user tự gửi (để lọc home + admin)
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

        return redirect()->route('my-articles.index')->with('success', __('messages.flash.story.submitted'));
    }

    /** Form sửa. */
    public function edit($id)
    {
        $article = $this->ownArticle($id);
        return view('client.my-articles.form', [
            'article'      => $article,
            'genres'       => Genre::orderBy('name')->get(),
            'countries'    => Country::orderBy('sort_order')->get(),
            'myCharacters' => Character::orderBy('name')->get(), // nhân vật dùng chung toàn site
            'myTeams'      => $this->selectableTeams($article->team_id),
            'mode'         => 'edit',
        ]);
    }

    /** Cập nhật. */
    public function update(Request $request, $id)
    {
        $article = $this->ownArticle($id);
        $data = $this->validateData($request, $article);
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

        return redirect()->route('my-articles.index')->with('success', __('messages.flash.story.updated'));
    }

    /** Xoá. */
    public function destroy($id)
    {
        $article = $this->ownArticle($id);
        abort_unless($this->canDeleteArticle($article), 403, 'You do not have permission to delete this story.');
        $article->genres()->detach();
        $article->authors()->detach();
        // Xoá CẢ chương hẹn giờ (không để sót do global scope).
        $article->chapters()->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class)->delete();
        $article->delete();

        return redirect()->route('my-articles.index')->with('success', __('messages.flash.story.deleted'));
    }

    /** Form thêm chương. */
    public function createChapter($id)
    {
        $article = $this->ownArticle($id);
        $nextNumber = (int) $article->chapters()
            ->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class)
            ->max('number') + 1;
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
            'published_at' => ['nullable', 'string', function ($attr, $value, $fail) {
                if (trim((string) $value) !== '' && Chapter::parsePublishedAt($value) === null) {
                    $fail('Định dạng lịch đăng không hợp lệ. Dùng YYYY-MM-DD HH:MM (vd 2026-06-15 08:00).');
                }
            }],
        ], [], ['number' => 'số chương', 'title' => 'tiêu đề', 'content' => 'nội dung']);

        $chapter = Chapter::create([
            'article_id'  => $article->id,
            'number'      => $data['number'],
            'title'       => $data['title'],
            'content'     => $data['content'],
            'credit_cost' => $data['credit_cost'] !== '' ? ($data['credit_cost'] ?? null) : null,
            'published_at' => Chapter::parsePublishedAt($request->input('published_at')),
        ]);
        $article->touch();
        $chapter->dispatchNewChapterNotification(); // báo người theo dõi (bỏ qua nếu hẹn giờ)

        return redirect()->route('my-articles.index')->with('success', __('messages.flash.story.chapter_added'));
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
        if (array_key_exists('team_id', $data) && $data['team_id'] === '') {
            $data['team_id'] = null;
        }
        return $data;
    }

    private function validateData(Request $request, ?Article $article = null): array
    {
        $allowedTeamIds = $this->selectableTeams($article?->team_id)->pluck('id')->all();

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
            'characters.*'    => ['integer', 'exists:characters,id'], // nhân vật dùng chung toàn site
            'cover_image'     => ['nullable', 'image', 'max:4096'],
            'background'      => ['nullable', 'image', 'max:6144'],
            'author_name'         => ['nullable', 'string', 'max:255'],
            'tags'                => ['nullable', 'string', 'max:1000'],
            'team_id'             => [
                'nullable',
                'integer',
                Rule::exists('teams', 'id')
                    ->where(fn ($query) => $query->where('status', Team::STATUS_APPROVED)),
                Rule::in($allowedTeamIds),
            ],
            'credit_start_chapter' => ['nullable', 'integer', 'min:1'],
            'credit_per_chapter'   => ['nullable', 'integer', 'min:0'],
        ], [], [
            'title' => 'tên truyện', 'description' => 'mô tả', 'cover_image' => 'ảnh bìa',
        ]);
    }

    private function resolveBackground(Request $request): ?string
    {
        if ($request->hasFile('background')) {
            return $this->storePublicImage($request->file('background'), 'images/articles');
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
            return $this->storePublicImage($request->file('cover_image'), 'images/articles');
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

    private function manageableTeamIds(array $roles = ['leader', 'admin', 'editor'])
    {
        return Team::query()
            ->where('status', Team::STATUS_APPROVED)
            ->where(function ($q) use ($roles) {
                $q->where('user_id', Auth::id())
                    ->orWhereHas('members', function ($m) use ($roles) {
                        $m->where('user_id', Auth::id())
                            ->where('status', 'approved')
                            ->whereIn('role', $roles);
                    });
            })
            ->pluck('id');
    }

    private function selectableTeams(?int $includeTeamId = null)
    {
        $teamIds = $this->manageableTeamIds();

        if ($teamIds->isEmpty() && !$includeTeamId) {
            return collect();
        }

        return Team::query()
            ->where('status', Team::STATUS_APPROVED)
            ->where(function ($q) use ($teamIds, $includeTeamId) {
                if ($teamIds->isNotEmpty()) {
                    $q->whereIn('id', $teamIds);
                }

                if ($includeTeamId) {
                    $teamIds->isNotEmpty()
                        ? $q->orWhere('id', $includeTeamId)
                        : $q->where('id', $includeTeamId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function canManageArticle(Article $article): bool
    {
        if ($article->user_id === Auth::id()) {
            return true;
        }

        return $article->team_id
            && $this->manageableTeamIds()->contains((int) $article->team_id);
    }

    private function canDeleteArticle(Article $article): bool
    {
        if ($article->user_id === Auth::id()) {
            return true;
        }

        return $article->team_id
            && $this->manageableTeamIds(['leader', 'admin'])->contains((int) $article->team_id);
    }
}
