<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Models\FaqComment;
use App\Models\FaqArticle;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = FaqComment::with(['article.category', 'user'])->orderBy('created_at', 'desc');

        // Search by content
        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        // Filter by article
        if ($request->filled('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        $comments = $query->paginate(50);

        return view('admin.faq.comments.index', compact('comments'));
    }

    public function destroy(FaqComment $comment)
    {
        // Update parent's reply_count if this is a reply
        if ($comment->parent_id) {
            $parent = FaqComment::find($comment->parent_id);
            if ($parent) {
                $parent->decrement('reply_count');
            }
        }

        // Update article's comment_count
        $article = $comment->article;
        if ($article) {
            $article->decrement('comment_count');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:faq_comments,id',
        ]);

        $deleted = FaqComment::whereIn('id', $request->comment_ids)->delete();

        // Recalculate comment counts
        $affectedArticles = FaqArticle::whereHas('comments')->get();
        foreach ($affectedArticles as $article) {
            $article->update(['comment_count' => $article->comments()->count()]);
        }

        return redirect()->back()->with('success', "$deleted comments deleted successfully.");
    }
}
