<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\StaticPageComment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StaticPageCommentController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('static_page_comments')) {
            $comments = new LengthAwarePaginator([], 0, 30, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
            $pageTypes = collect();

            return view('admin.static-page-comments.index', compact('comments', 'pageTypes'))
                ->with('schemaWarning', true)
                ->with('supportsHidden', false);
        }

        $supportsHidden = Schema::hasColumn('static_page_comments', 'is_hidden');
        $query = StaticPageComment::with([
            'user:id,name,username',
            'page:id,title_en,title_vi,slug,page_type',
            'parent:id,user_id',
        ]);

        if ($search = trim((string) $request->get('q'))) {
            $query->where('content', 'like', "%{$search}%");
        }

        if ($pageType = $request->get('page_type')) {
            $query->whereHas('page', fn ($q) => $q->where('page_type', $pageType));
        }

        $comments = $query->latest()->paginate(30)->withQueryString();
        $pageTypes = StaticPage::whereHas('comments')
            ->whereNotNull('page_type')
            ->distinct()
            ->orderBy('page_type')
            ->pluck('page_type');

        return view('admin.static-page-comments.index', compact('comments', 'pageTypes', 'supportsHidden'))
            ->with('schemaWarning', !$supportsHidden);
    }

    public function update(Request $request, StaticPageComment $comment)
    {
        $comment->update($this->validatedContent($request));

        return back()->with('success', __('messages.flash.comment.updated'));
    }

    public function reply(Request $request, StaticPageComment $comment)
    {
        $root = $comment->parent ?: $comment;
        $data = $this->validatedContent($request);

        StaticPageComment::create([
            'static_page_id' => $root->static_page_id,
            'user_id' => Auth::id(),
            'parent_id' => $root->id,
            'content' => $data['content'],
        ]);

        return back()->with('success', __('messages.flash.comment.replied'));
    }

    public function toggleHidden(StaticPageComment $comment)
    {
        $comment->update(['is_hidden' => !$comment->is_hidden]);

        return back()->with(
            'success',
            __('messages.flash.comment.' . ($comment->is_hidden ? 'hidden' : 'shown'))
        );
    }

    public function destroy(StaticPageComment $comment)
    {
        DB::transaction(fn () => $comment->delete());

        return back()->with('success', __('messages.flash.comment.deleted'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:static_page_comments,id'],
        ])['ids'];

        $deleted = DB::transaction(function () use ($ids) {
            $comments = StaticPageComment::whereIn('id', $ids)->get(['id']);
            StaticPageComment::whereIn('id', $comments->pluck('id'))->delete();

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
}
