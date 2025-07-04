<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
{
    public function show(Article $article, $number)
    {
        $chapter = $article->chapters()->where('number', $number)->first();
        if (empty($chapter)) {
            abort(404);
        }

        $userIdentifier = Auth::check() ? Auth::id() : 'guest';
        $sessionBase = "chapter_{$userIdentifier}_{$article->id}";
        $adClickedKey = "{$sessionBase}_ad_clicked";
        $popupActiveKey = "{$sessionBase}_popup_active";
        $lastPopupChapterKey = "{$sessionBase}_last_popup_chapter";

        $chapterViewCount = Session::get("{$sessionBase}_view_count", 0) + 1;
        Session::put("{$sessionBase}_view_count", $chapterViewCount);

        $showPopup = false;

        $hasActiveVip = false;
        if (Auth::check()) {
            $hasActiveVip = \App\Models\UserVip::where('user_id', Auth::id())
                ->where('end_at', '>=', now())
                ->exists();
        }

        if (!$hasActiveVip && $number >= 2) {
            $lastPopupChapter = Session::get($lastPopupChapterKey, 0);
            $adClicked = Session::get($adClickedKey, false);
            $popupActive = Session::get($popupActiveKey, false);

            if ($popupActive && !$adClicked) {
                $showPopup = true;
            } elseif ($lastPopupChapter == 0) {
                Session::put($popupActiveKey, true);
                Session::put($lastPopupChapterKey, $number);
                $showPopup = true;
            } elseif ($adClicked && $number % 2 == 0 && $number > $lastPopupChapter) {
                Session::put($popupActiveKey, true);
                Session::put($lastPopupChapterKey, $number);
                Session::forget($adClickedKey);
                $showPopup = true;
            }
        }

        if (empty($article->affi_link) || empty($article->affi_image)) {
            $showPopup = false;
        }

        $chapter->increaseViewCount();
        $article->increaseViewCount();

        $comments = $article->getNewestCommentsPaginate();

        return view('client.chapters.show', [
            'article' => $article,
            'chapter' => $chapter,
            'articleChapters' => $article->chapters()->orderBy('number', 'desc')->get(),
            'user' => $article->user,
            'comments' => $comments,
            'showPopup' => $showPopup,
            'adClickedKey' => $adClickedKey,
            'affiLink' => $article->affi_link,
            'affiImage' => $article->affi_image,
            'isUserLoggedIn' => Auth::check(),
        ]);
    }

    public function markAdClicked(Request $request, Article $article, $number)
    {
        $chapter = $article->chapters()->where('number', $number)->first();
        if (empty($chapter)) {
            return response()->json(['success' => false, 'error' => 'Không tìm thấy chương'], 404);
        }

        $userIdentifier = Auth::check() ? Auth::id() : 'guest';
        $sessionBase = "chapter_{$userIdentifier}_{$article->id}";
        $adClickedKey = "{$sessionBase}_ad_clicked";
        $popupActiveKey = "{$sessionBase}_popup_active";

        Session::put($adClickedKey, true);
        Session::forget($popupActiveKey);

        return response()->json(['success' => true]);
    }
}
