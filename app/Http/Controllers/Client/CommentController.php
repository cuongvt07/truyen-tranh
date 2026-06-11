<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\CommentVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Article $article)
    {
        $data = $request->validate([
            'content'   => 'required|min:1|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Reply chỉ lồng 1 cấp: nếu cha đã là reply thì gắn lên gốc của nó
        $parentId = null;
        if (!empty($data['parent_id'])) {
            $parent = Comment::where('article_id', $article->id)->find($data['parent_id']);
            if ($parent) {
                $parentId = $parent->parent_id ?? $parent->id;
            }
        }

        $comment = Comment::create([
            'user_id'    => Auth::id(),
            'article_id' => $article->id,
            'parent_id'  => $parentId,
            'content'    => $data['content'],
        ]);

        if ($parentId) {
            Comment::where('id', $parentId)->increment('replies_count');
        }

        if ($request->wantsJson() || $request->ajax()) {
            $comment->load('user');
            return response()->json([
                'ok'      => true,
                'html'    => view('client.articles.partials.comment', ['comment' => $comment, 'isReply' => (bool) $parentId])->render(),
                'isReply' => (bool) $parentId,
                'parent'  => $parentId,
            ]);
        }

        return redirect()->to(url()->previous() . '#comments')->with('success', __('messages.comments.title'));
    }

    public function destroy(Article $article, Comment $comment)
    {
        abort_unless($comment->canBeDeletedBy(Auth::user()), 403);

        if ($comment->parent_id) {
            Comment::where('id', $comment->parent_id)->where('replies_count', '>', 0)->decrement('replies_count');
        }
        $comment->delete(); // cascade removes replies + votes + reports

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->back();
    }

    /** Vote up/down (toggle). value: 1 or -1 */
    public function vote(Request $request, Comment $comment)
    {
        if (!Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Please log in.'], 401);
        }
        $value  = (int) $request->input('value') === -1 ? -1 : 1;
        $userId = Auth::id();

        $existing = CommentVote::where('comment_id', $comment->id)->where('user_id', $userId)->first();
        $current  = $existing ? (int) $existing->value : 0;

        if ($value === -1) {
            // Giảm chỉ để huỷ phiếu tăng hiện có; chưa tăng thì không được giảm.
            if ($current === 1) {
                $existing->delete();
                $myVote = 0;
            } else {
                $myVote = $current;        // no-op: không cho downvote khi chưa upvote
            }
        } else {
            // Tăng: bấm lại để huỷ.
            if ($current === 1) {
                $existing->delete();
                $myVote = 0;
            } else {
                CommentVote::updateOrCreate(
                    ['comment_id' => $comment->id, 'user_id' => $userId],
                    ['value' => 1]
                );
                $myVote = 1;
            }
        }

        $score = (int) CommentVote::where('comment_id', $comment->id)->sum('value');
        $comment->forceFill(['score' => $score])->saveQuietly();

        return response()->json(['ok' => true, 'score' => $score, 'myVote' => $myVote]);
    }

    /** Report a comment */
    public function report(Request $request, Comment $comment)
    {
        if (!Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Please log in.'], 401);
        }
        $reason = $request->input('reason');

        CommentReport::updateOrCreate(
            ['comment_id' => $comment->id, 'user_id' => Auth::id()],
            ['reason' => $reason ? mb_substr($reason, 0, 255) : null]
        );

        return response()->json(['ok' => true, 'message' => __('messages.comments.report_thanks')]);
    }
}
