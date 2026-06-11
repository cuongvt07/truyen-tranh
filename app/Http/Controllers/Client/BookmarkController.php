<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    private const STATUSES = ['reading', 'planning', 'dropped', 'completed', 'paused', 'remove'];

    public function store(Request $request, Article $article)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|in:' . implode(',', self::STATUSES),
        ]);

        $status = $request->input('status', 'reading');
        $wantsJson = $request->ajax() || $request->wantsJson();

        if ($status === 'remove') {
            Bookmark::where('user_id', Auth::id())
                ->where('article_id', $article->id)
                ->delete();

            if ($wantsJson) {
                return response()->json(['success' => true, 'status' => null]);
            }

            return redirect()->back()->with('bookmark_success', __('messages.article.list_removed_success'));
        }

        Bookmark::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'article_id' => $article->id,
            ],
            [
                'name' => $request->input('name') ?: $article->title . ' #' . $article->id,
                'description' => $request->input('description'),
                'is_public' => $request->boolean('is_public', true),
                'status' => $status,
            ]
        )->update(['status' => $status]);

        if ($wantsJson) {
            return response()->json(['success' => true, 'status' => $status]);
        }

        return redirect()->back()->with('bookmark_success', __('messages.article.list_added_success'));
    }

    public function destroy(Article $article, Bookmark $bookmark)
    {
        abort_unless(
            $bookmark->article_id === $article->id && $bookmark->user_id === Auth::id(),
            404
        );

        $bookmark->delete();

        return redirect()->back()->with('bookmark_success', __('messages.article.list_removed_success'));
    }
}
