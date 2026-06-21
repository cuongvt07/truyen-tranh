<?php

namespace App\Http\Controllers\Admin\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = ForumComment::with(['post.category', 'user'])->orderBy('created_at', 'desc');

        // Search by content
        if ($request->filled('q')) {
            $query->where('content', 'like', '%' . $request->q . '%');
        }

        // Filter by post
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        $comments = $query->paginate(30)->withQueryString();
        $sources = ForumPost::orderByDesc('id')->limit(500)->get(['id', 'title_en', 'title_vi']);

        return view('admin.forum.comments.index', compact('comments', 'sources'));
    }

    public function destroy(ForumComment $comment)
    {
        DB::transaction(function () use ($comment) {
            $postId = $comment->post_id;
            $parentId = $comment->parent_id;
            $comment->delete();
            $this->syncCounts([$postId], $parentId ? [$parentId] : []);
        });

        return back()->with('success', __('messages.flash.comment.deleted'));
    }

    public function update(Request $request, ForumComment $comment)
    {
        $comment->update($this->validatedContent($request));

        return back()->with('success', __('messages.flash.comment.updated'));
    }

    public function reply(Request $request, ForumComment $comment)
    {
        $root = $comment->parent ?: $comment;
        $data = $this->validatedContent($request);

        ForumComment::create([
            'post_id' => $root->post_id,
            'user_id' => Auth::id(),
            'parent_id' => $root->id,
            'content' => $data['content'],
        ]);
        $this->syncCounts([$root->post_id], [$root->id]);

        return back()->with('success', __('messages.flash.comment.replied'));
    }

    public function toggleHidden(ForumComment $comment)
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
            'ids.*' => ['integer', 'exists:forum_comments,id'],
        ]);

        $deleted = DB::transaction(function () use ($request) {
            $comments = ForumComment::whereIn('id', $request->input('ids'))->get(['id', 'post_id', 'parent_id']);
            $postIds = $comments->pluck('post_id')->unique()->all();
            $parentIds = $comments->pluck('parent_id')->filter()->unique()->all();
            ForumComment::whereIn('id', $comments->pluck('id'))->delete();
            $this->syncCounts($postIds, $parentIds);

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

    private function syncCounts(array $postIds, array $parentIds = []): void
    {
        ForumPost::whereIn('id', array_filter($postIds))->get()->each(
            fn (ForumPost $post) => $post->update(['comment_count' => $post->comments()->count()])
        );

        ForumComment::whereIn('id', array_filter($parentIds))->get()->each(
            fn (ForumComment $parent) => $parent->update(['reply_count' => $parent->replies()->count()])
        );
    }
}
