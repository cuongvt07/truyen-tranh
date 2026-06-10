<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ChapterUnlock;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ChapterController extends Controller
{
    public function show(Article $article, $number)
    {
        if (request()->route()->originalParameter('article') !== $article->getRouteKey()) {
            return redirect()->route('articles.chapters.show', [$article, $number], 301);
        }

        $chapter = $article->chapters()->where('number', $number)->first();
        if (empty($chapter)) {
            abort(404);
        }

        $number = (int) $number;

        $hasActiveVip = false;
        if (Auth::check()) {
            $hasActiveVip = \App\Models\UserVip::where('user_id', Auth::id())
                ->where('end_at', '>=', now())
                ->exists();
        }

        // --- Credit gate ---
        $creditCost = $chapter->getEffectiveCreditCost($article);
        $isLocked = false;
        $alreadyUnlocked = false;

        if ($creditCost > 0 && !$hasActiveVip) {
            if (!Auth::check()) {
                $isLocked = true;
            } else {
                $alreadyUnlocked = ChapterUnlock::hasUnlocked(Auth::id(), $chapter->id);
                $isLocked = !$alreadyUnlocked;
            }
        }

        // If locked: show gate view (no view count increment, no reading history)
        if ($isLocked) {
            return view('client.chapters.locked', [
                'article'    => $article,
                'chapter'    => $chapter,
                'creditCost' => $creditCost,
                'userPoints' => Auth::check() ? Auth::user()->points : 0,
            ]);
        }

        // --- Ads (VIP sees none) ---
        $chapterAd = null;
        $chapterAds = $hasActiveVip ? collect() : (\App\Models\Ad::forPageGrouped('chapter')->get('chapter') ?? collect());
        foreach ($chapterAds as $ad) {
            if ($ad->hide_for_vip && $hasActiveVip) {
                continue;
            }
            if ($number < $ad->chapter_start) {
                continue;
            }
            $interval = max(1, (int) $ad->chapter_interval);
            if ((($number - $ad->chapter_start) % $interval) !== 0) {
                continue;
            }
            if (!$ad->image && !$ad->link) {
                continue;
            }
            $chapterAd = $ad;
            break;
        }

        $showPopup    = $chapterAd !== null;
        $requireClick = (bool) ($chapterAd->require_click ?? false);

        // Đếm view
        $chapter->increaseViewCount();
        $article->increaseViewCount();

        // Ghi lịch sử đọc
        if (Auth::check()) {
            ReadingHistory::record(Auth::id(), $article->id, $chapter->id, $number);
        }

        $comments = $article->getNewestCommentsPaginate();

        return view('client.chapters.show', [
            'article'        => $article,
            'chapter'        => $chapter,
            'articleChapters' => $article->chapters()->orderBy('number', 'desc')->get(),
            'user'           => $article->user,
            'comments'       => $comments,
            'showPopup'      => $showPopup,
            'requireClick'   => $requireClick,
            'affiLink'       => $chapterAd?->link ?? '',
            'affiImage'      => $chapterAd?->image ?? '',
            'isUserLoggedIn' => Auth::check(),
            'creditCost'     => $creditCost,
            'alreadyUnlocked' => $alreadyUnlocked,
        ]);
    }

    /**
     * Deduct credits and record unlock for a paid chapter.
     */
    public function unlock(Request $request, Article $article, $number)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Bạn cần đăng nhập.'], 401);
        }

        $chapter = $article->chapters()->where('number', $number)->first();
        if (!$chapter) {
            return response()->json(['success' => false, 'error' => 'Không tìm thấy chương.'], 404);
        }

        $hasActiveVip = \App\Models\UserVip::where('user_id', Auth::id())
            ->where('end_at', '>=', now())
            ->exists();

        $creditCost = $chapter->getEffectiveCreditCost($article);

        // Already free or VIP
        if ($creditCost === 0 || $hasActiveVip) {
            return response()->json(['success' => true, 'redirect' => route('articles.chapters.show', [$article, $number])]);
        }

        // Already unlocked
        if (ChapterUnlock::hasUnlocked(Auth::id(), $chapter->id)) {
            return response()->json(['success' => true, 'redirect' => route('articles.chapters.show', [$article, $number])]);
        }

        $user = Auth::user();
        if ($user->points < $creditCost) {
            return response()->json(['success' => false, 'error' => 'Không đủ credit. Vui lòng nạp thêm.'], 422);
        }

        DB::transaction(function () use ($user, $chapter, $article, $creditCost) {
            $user->decrement('points', $creditCost);
            ChapterUnlock::create([
                'user_id'       => $user->id,
                'chapter_id'    => $chapter->id,
                'article_id'    => $article->id,
                'credits_spent' => $creditCost,
            ]);
        });

        return response()->json([
            'success'  => true,
            'redirect' => route('articles.chapters.show', [$article, $number]),
            'points'   => $user->fresh()->points,
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
