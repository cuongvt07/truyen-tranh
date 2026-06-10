<?php

namespace App\Http\Controllers\Admin\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use App\Models\ForumPost;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = ForumComment::with(['post.category', 'user'])->orderBy('created_at', 'desc');

        // Search by content
        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        // Filter by post
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        $comments = $query->paginate(50);

        return view('admin.forum.comments.index', compact('comments'));
    }

    public function destroy(ForumComment $comment)
    {
        // Update parent's reply_count if this is a reply
        if ($comment->parent_id) {
            $parent = ForumComment::find($comment->parent_id);
            if ($parent) {
                $parent->decrement('reply_count');
            }
        }

        // Update post's comment_count
        $post = $comment->post;
        if ($post) {
            $post->decrement('comment_count');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:forum_comments,id',
        ]);

        $deleted = ForumComment::whereIn('id', $request->comment_ids)->delete();

        // Recalculate comment counts (simple approach)
        $affectedPosts = ForumPost::whereHas('comments')->get();
        foreach ($affectedPosts as $post) {
            $post->update(['comment_count' => $post->comments()->count()]);
        }

        return redirect()->back()->with('success', "$deleted comments deleted successfully.");
    }
}
