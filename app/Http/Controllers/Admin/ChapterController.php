<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Chapter\StoreChapterRequest;
use App\Http\Requests\Admin\Chapter\UpdateChapterRequest;
use App\Models\Article;
use App\Models\Chapter;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    /**
     * Trang "Chương": list THEO TRUYỆN (mỗi truyện 1 dòng + số chương),
     * bấm "Chi tiết" để vào danh sách chương của truyện đó.
     */
    public function allIndex(Request $request)
    {
        $noScope = fn ($q) => $q->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class);
        $query = Article::withoutGlobalScope(\App\Scopes\ApprovedArticleScope::class)
            ->withCount(['chapters' => $noScope])
            ->withMax(['chapters' => $noScope], 'created_at');

        if ($s = trim((string) $request->get('q'))) {
            $query->where('title', 'like', "%$s%");
        }

        $perPageOptions = [20, 50, 100, 200];
        $perPage = (int) $request->get('per_page', 20);
        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 20;
        }

        $articles = $query->orderByDesc('chapters_count')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.chapters.all', compact('articles', 'perPage', 'perPageOptions'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Article $article)
    {
        $chapters = $article->chapters()
            ->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class)
            ->orderByDesc("number");
        if ($request->filled('search')) {
            $searchText = $request->input('search');
            $chapters->where('title', 'like', '%'.$searchText.'%');
        }

        $perPageOptions = [20, 50, 100, 200];
        $perPage = (int) $request->get('per_page', 20);
        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 20;
        }

        $chapters = $chapters->paginate($perPage)->withQueryString();
        return view('admin.chapters.index', [
            'article' => $article,
            'chapters' => $chapters,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Article $article)
    {
        $chapter = new Chapter();
        // Tính cả chương hẹn giờ để không trùng số.
        $maxNumber = $article->chapters()
            ->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class)
            ->max('number');
        $chapter->number = $maxNumber ? $maxNumber + 1 : 1;
        return view('admin.chapters.create', [
            'article' => $article,
            'chapter' => $chapter,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChapterRequest $request, Article $article)
    {
        $request->validated();
        $validateData = $request->all();
        $validateData['article_id'] = $article->id;
        // Lịch đăng: parse chuỗi text (có thể paste từ Excel) -> datetime; rỗng = đăng ngay.
        $validateData['published_at'] = Chapter::parsePublishedAt($request->input('published_at'));
        $chapter = Chapter::create($validateData);
        $article->setUpdatedAt(now());
        $article->save();
        return redirect()->route('admin.articles.show_chapters', $article->id)
            ->with('success', __('messages.flash.chapter.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chapter $chapter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article, Chapter $chapter)
    {
        return view('admin.chapters.edit', [
            'chapter' => $chapter,
            'article' => $article,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateChapterRequest $request,
        Article $article,
        Chapter $chapter
    ) {
        $request->validated();
        $validateData = $request->all();
        // Lịch đăng: parse text -> datetime; ô rỗng = đăng ngay (published_at = null).
        $validateData['published_at'] = Chapter::parsePublishedAt($request->input('published_at'));
        $chapter->update($validateData);
        return redirect()->route('admin.articles.show_chapters', $article->id)
            ->with('success', __('messages.flash.chapter.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article, Chapter $chapter)
    {
        $chapter->delete();
        return redirect()->route('admin.articles.show_chapters', $article->id)
            ->with('success', __('messages.flash.chapter.deleted'));
    }
}
