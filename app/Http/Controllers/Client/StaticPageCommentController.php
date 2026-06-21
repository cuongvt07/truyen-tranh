<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\StaticPageComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaticPageCommentController extends Controller
{
    // POST /static-pages/{staticPage}/comments
    public function store(Request $request, StaticPage $staticPage)
    {
        abort_unless($staticPage->is_active && $staticPage->comments_enabled, 403);

        $validated = $request->validate([
            'content'   => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:static_page_comments,id'],
        ]);

        if (!empty($validated['parent_id'])) {
            abort_unless(
                StaticPageComment::where('id', $validated['parent_id'])
                    ->where('static_page_id', $staticPage->id)
                    ->where('is_hidden', false)
                    ->exists(),
                422
            );
        }

        StaticPageComment::create([
            'static_page_id' => $staticPage->id,
            'user_id'        => Auth::id(),
            'parent_id'      => $validated['parent_id'] ?? null,
            'content'        => $validated['content'],
        ]);

        return back()->with('success', 'Comment posted.');
    }

    // DELETE /static-pages/{staticPage}/comments/{comment}
    public function destroy(StaticPage $staticPage, StaticPageComment $comment)
    {
        abort_unless($comment->static_page_id === $staticPage->id, 404);

        $user = Auth::user();

        // Chủ sở hữu hoặc admin được xoá
        abort_unless($user->id === $comment->user_id || $user->is_admin, 403);

        $comment->delete();

        return back()->with('success', __('messages.flash.comment.deleted'));
    }
}
