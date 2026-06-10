<?php

namespace App\Http\Controllers\Client\Faq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\StoreCommentRequest;
use App\Models\FaqArticle;
use App\Models\FaqComment;
use App\Models\FaqCommentVote;
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
    public function store(StoreCommentRequest $request, FaqArticle $article)
    {
        abort_unless(
            $article->comments_enabled, 
            403, 
            'Comments are disabled for this article.'
        );

        $validated = $request->validated();

        // Verify parent comment belongs to this article if replying
        if (!empty($validated['parent_id'])) {
            $parent = FaqComment::where('id', $validated['parent_id'])
                ->where('article_id', $article->id)
                ->firstOrFail();
        }

        $comment = FaqComment::create([
            'article_id' => $article->id,
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        // Increment counters
        $article->increment('comment_count');
        
        if (!empty($validated['parent_id'])) {
            FaqComment::where('id', $validated['parent_id'])->increment('reply_count');
        }

        return back()->with('success', app()->getLocale() === 'vi' 
            ? 'Bình luận đã được đăng.' 
            : 'Comment posted successfully.');
    }

    /**
     * Delete a comment
     */
    public function destroy(FaqComment $comment)
    {
        abort_unless(
            $comment->canBeDeletedBy(auth()->user()),
            403,
            'You do not have permission to delete this comment.'
        );

        $article = $comment->article;
        $parentId = $comment->parent_id;
        $replyCount = $comment->replies()->count();

        // Delete the comment (cascade will handle replies)
        $comment->delete();

        // Update counters
        $article->decrement('comment_count', 1 + $replyCount);
        
        if ($parentId) {
            FaqComment::where('id', $parentId)->decrement('reply_count');
        }

        return back()->with('success', app()->getLocale() === 'vi' 
            ? 'Bình luận đã được xóa.' 
            : 'Comment deleted successfully.');
    }

    /**
     * Vote on a comment (upvote or downvote)
     */
    public function vote(Request $request, FaqComment $comment)
    {
        $validated = $request->validate([
            'value' => ['required', 'integer', 'in:1,-1'],
        ]);

        $userId = auth()->id();
        $value = (int) $validated['value'];

        // Check if user already voted
        $existingVote = FaqCommentVote::where('comment_id', $comment->id)
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
            FaqCommentVote::create([
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
