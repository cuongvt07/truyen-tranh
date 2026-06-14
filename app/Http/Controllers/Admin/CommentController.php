<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Scopes\ApprovedArticleScope;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /** Danh sách bình luận bị report (gom theo bình luận). */
    public function reports(Request $request)
    {
        $onlyOpen = $request->get('filter', 'open') !== 'all';

        $comments = Comment::query()
            ->withCount([
                'reports',
                'reports as open_reports_count' => fn ($q) => $q->where('resolved', false),
            ])
            ->with([
                'user:id,name,username',
                // bỏ scope duyệt để thấy cả truyện chưa duyệt/ẩn
                'article' => fn ($q) => $q->withoutGlobalScope(ApprovedArticleScope::class)->select('id', 'title'),
                'reports' => fn ($q) => $q->latest()->with('user:id,name,username'),
            ])
            ->having('reports_count', '>', 0)
            ->when($onlyOpen, fn ($q) => $q->having('open_reports_count', '>', 0))
            ->orderByDesc('open_reports_count')
            ->orderByDesc('reports_count')
            ->paginate(20)
            ->withQueryString();

        $openTotal = CommentReport::where('resolved', false)->count();

        return view('admin.comments.reports', compact('comments', 'openTotal', 'onlyOpen'));
    }

    /** Đánh dấu tất cả report của 1 bình luận là đã xử lý. */
    public function resolveReports(Comment $comment)
    {
        $comment->reports()->update(['resolved' => true]);
        return back()->with('success', __('messages.flash.comment.report_resolved'));
    }

    public function index(Request $request)
    {
        $q = Comment::query()->with(['user:id,name,username', 'article:id,title']);

        if ($s = trim((string) $request->get('q'))) {
            $q->where('content', 'like', "%$s%");
        }
        if ($articleId = $request->get('article_id')) {
            $q->where('article_id', (int) $articleId);
        }

        $comments = $q->orderByDesc('id')
            ->paginate($request->get('per_page', 20))->withQueryString();
        $total = Comment::count();
        $articles = Article::orderBy('title')->limit(500)->get(['id', 'title']);

        return view('admin.comments.index', compact('comments', 'total', 'articles'));
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return back()->with('success', __('messages.flash.comment.deleted'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (!empty($ids)) {
            Comment::whereIn('id', $ids)->delete();
        }
        return back()->with('success', __('messages.flash.comment.bulk_deleted', ['count' => count($ids)]));
    }
}
