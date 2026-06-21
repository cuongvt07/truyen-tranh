<?php

namespace App\Http\Controllers\Client\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\StoreCommentRequest;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumCommentVote;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new comment or reply
     */
    public function store(StoreCommentRequest $request, ForumPost $post)
    {
        abort_if($post->is_locked, 403, 'This post is locked and cannot accept comments.');

        $validated = $request->validated();

        // Verify parent comment belongs to this post if replying
        if (!empty($validated['parent_id'])) {
            $parent = ForumComment::where('id', $validated['parent_id'])
                ->where('post_id', $post->id)
                ->where('is_hidden', false)
                ->firstOrFail();
        }

        $comment = ForumComment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        // Increment counters
        $post->increment('comment_count');
        
        if (!empty($validated['parent_id'])) {
            ForumComment::where('id', $validated['parent_id'])->increment('reply_count');
        }

        return back()->with('success', app()->getLocale() === 'vi' 
            ? 'Bình luận đã được đăng.' 
            : 'Comment posted successfully.');
    }

    /**
     * Delete a comment
     */
    public function destroy(ForumComment $comment)
    {
        abort_unless(
            $comment->canBeDeletedBy(auth()->user()),
            403,
            'You do not have permission to delete this comment.'
        );

        $post = $comment->post;
        $parentId = $comment->parent_id;
        $replyCount = $comment->replies()->count();

        // Delete the comment (cascade will handle replies)
        $comment->delete();

        // Update counters
        $post->decrement('comment_count', 1 + $replyCount);
        
        if ($parentId) {
            ForumComment::where('id', $parentId)->decrement('reply_count');
        }

        return back()->with('success', app()->getLocale() === 'vi' 
            ? 'Bình luận đã được xóa.' 
            : 'Comment deleted successfully.');
    }

    /**
     * Vote on a comment (upvote or downvote)
     */
    public function vote(Request $request, ForumComment $comment)
    {
        abort_if($comment->is_hidden, 404);

        $validated = $request->validate([
            'value' => ['required', 'integer', 'in:1,-1'],
        ]);

        $userId = auth()->id();
        $value = (int) $validated['value'];

        // Check if user already voted
        $existingVote = ForumCommentVote::where('comment_id', $comment->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            if ($existingVote->value === $value) {
                // Remove vote if same value
                $existingVote->delete();
                $comment->decrement('score', $value);
            } else {
                // Update vote and adjust score by 2x (remove old, add new)
                $existingVote->update(['value' => $value]);
                $comment->increment('score', $value * 2);
            }
        } else {
            // Create new vote
            ForumCommentVote::create([
                'comment_id' => $comment->id,
                'user_id' => $userId,
                'value' => $value,
            ]);
            $comment->increment('score', $value);
        }

        return response()->json([
            'success' => true,
            'score' => $comment->fresh()->score,
        ]);
    }
}
