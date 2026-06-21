<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Models\FaqComment;
use App\Models\FaqArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = FaqComment::with(['article.category', 'user'])->orderBy('created_at', 'desc');

        // Search by content
        if ($request->filled('q')) {
            $query->where('content', 'like', '%' . $request->q . '%');
        }

        // Filter by article
        if ($request->filled('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        $comments = $query->paginate(30)->withQueryString();
        $sources = FaqArticle::orderByDesc('id')->limit(500)->get(['id', 'title_en', 'title_vi']);

        return view('admin.faq.comments.index', compact('comments', 'sources'));
    }

    public function destroy(FaqComment $comment)
    {
        DB::transaction(function () use ($comment) {
            $articleId = $comment->article_id;
            $parentId = $comment->parent_id;
            $comment->delete();
            $this->syncCounts([$articleId], $parentId ? [$parentId] : []);
        });

        return back()->with('success', __('messages.flash.comment.deleted'));
    }

    public function update(Request $request, FaqComment $comment)
    {
        $comment->update($this->validatedContent($request));

        return back()->with('success', __('messages.flash.comment.updated'));
    }

    public function reply(Request $request, FaqComment $comment)
    {
        $root = $comment->parent ?: $comment;
        $data = $this->validatedContent($request);

        FaqComment::create([
            'article_id' => $root->article_id,
            'user_id' => Auth::id(),
            'parent_id' => $root->id,
            'content' => $data['content'],
        ]);
        $this->syncCounts([$root->article_id], [$root->id]);

        return back()->with('success', __('messages.flash.comment.replied'));
    }

    public function toggleHidden(FaqComment $comment)
    {
        $comment->update(['is_hidden' => !$comment->is_hidden]);

        return back()->with(
            'success',
            __('messages.flash.comment.' . ($comment->is_hidden ? 'hidden' : 'shown'))
        );
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:faq_comments,id'],
        ]);

        $deleted = DB::transaction(function () use ($request) {
            $comments = FaqComment::whereIn('id', $request->input('ids'))->get(['id', 'article_id', 'parent_id']);
            $articleIds = $comments->pluck('article_id')->unique()->all();
            $parentIds = $comments->pluck('parent_id')->filter()->unique()->all();
            FaqComment::whereIn('id', $comments->pluck('id'))->delete();
            $this->syncCounts($articleIds, $parentIds);

            return $comments->count();
        });

        return back()->with('success', __('messages.flash.comment.bulk_deleted', ['count' => $deleted]));
    }

    private function validatedContent(Request $request): array
    {
        return $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);
    }

    private function syncCounts(array $articleIds, array $parentIds = []): void
    {
        FaqArticle::whereIn('id', array_filter($articleIds))->get()->each(
            fn (FaqArticle $article) => $article->update(['comment_count' => $article->comments()->count()])
        );

        FaqComment::whereIn('id', array_filter($parentIds))->get()->each(
            fn (FaqComment $parent) => $parent->update(['reply_count' => $parent->replies()->count()])
        );
    }
}
