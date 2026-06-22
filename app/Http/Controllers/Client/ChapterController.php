<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\ChapterLike;
use App\Models\ChapterParagraphBookmark;
use App\Models\ChapterReport;
use App\Models\ChapterUnlock;
use App\Models\ReadingHistory;
use App\Services\ReadingAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ChapterController extends Controller
{
    public function show(Article $article, $number, Request $request, ReadingAccessService $readingAccess)
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

        // Locked teaser pages are not reads and do not consume either quota.
        if (!$isLocked) {
            if (!Auth::check() && !$readingAccess->guestCanRead($request, $article->id, $chapter->id)) {
                return redirect()->guest(route('login'))
                    ->with('reading_limit_notice', __('messages.chapter.guest_reading_limit', [
                        'articles' => $readingAccess->guestArticleLimit(),
                        'chapters' => $readingAccess->guestChapterLimit(),
                    ]));
            }

            if (Auth::check() && !$readingAccess->unpaidUserCanRead(Auth::user(), $chapter->id)) {
                return redirect()->route('pages.pricing')
                    ->with('reading_limit_notice', __('messages.chapter.unpaid_reading_limit', [
                        'chapters' => $readingAccess->unpaidUserChapterLimit(),
                    ]));
            }
        }

        // Chương khoá: vẫn vào trang đọc nhưng chỉ hiện teaser mờ + paywall (xử lý ở view).

        // --- Ads (VIP sees none) ---
        $chapterAds = $hasActiveVip ? collect() : (Ad::forPageGrouped('chapter')->get('chapter') ?? collect());
        $chapterAds = $chapterAds->filter(function (Ad $ad) use ($hasActiveVip, $number) {
            if ($ad->hide_for_vip && $hasActiveVip) {
                return false;
            }
            $hasActiveItem = $ad->items
                ->where('is_active', true)
                ->first(fn ($item) => $item->image || $item->script_code);

            if (!$ad->image && !$ad->script_code && !$ad->link && !$hasActiveItem) {
                return false;
            }

            return true;
        })->values();

        $chapterAd = $chapterAds->first(fn (Ad $ad) => $ad->require_click);
        $inlineCampaign = $chapterAds->first(fn (Ad $ad) => !$ad->require_click);
        $inlineChapterAds = collect();
        $inlineAdFirstAfter = 4;
        $inlineAdEvery = 8;

        if ($inlineCampaign) {
            $inlineCount = max(1, (int) ($inlineCampaign->chapter_inline_count ?: 1));
            $inlineItems = $inlineCampaign->items
                ->where('is_active', true)
                ->filter(fn ($item) => $item->image || $item->script_code)
                ->values();

            if ($inlineItems->isEmpty() && ($inlineCampaign->image || $inlineCampaign->script_code)) {
                $inlineItems = collect([$inlineCampaign]);
            }

            $inlineChapterAds = $inlineItems->shuffle()->take($inlineCount)->values();
        }

        $showPopup    = $chapterAd !== null;
        $requireClick = (bool) ($chapterAd->require_click ?? false);
        $popupAdItem = $chapterAd
            ? $chapterAd->items->where('is_active', true)->filter(fn ($item) => $item->image || $item->script_code)->shuffle()->first()
            : null;

        $chapterLikesCount = ChapterLike::where('chapter_id', $chapter->id)->count();
        $userLikedChapter = false;
        $hasStartedReading = false;
        $currentListStatus = null;
        $bookmarkParagraph = 0;
        if (Auth::check()) {
            $userLikedChapter = ChapterLike::where('user_id', Auth::id())
                ->where('chapter_id', $chapter->id)
                ->exists();
            $hasStartedReading = ReadingHistory::where('user_id', Auth::id())
                ->where('article_id', $article->id)
                ->exists();
            $currentListStatus = Bookmark::where('user_id', Auth::id())
                ->where('article_id', $article->id)
                ->value('status');
            $bookmarkParagraph = (int) ChapterParagraphBookmark::where('user_id', Auth::id())
                ->where('chapter_id', $chapter->id)
                ->value('paragraph');
        }

        if ($isLocked) {
            // Teaser: không tăng view, không ghi lịch sử, tắt popup + inline ad.
            $showPopup = false;
            $inlineChapterAds = collect();
        } else {
            if (!Auth::check()) {
                $readingAccess->recordGuestRead($request, $article->id, $chapter->id);
            }

            // Đếm view
            $chapter->increaseViewCount();
            $article->increaseViewCount();

            // Ghi lịch sử đọc
            if (Auth::check()) {
                ReadingHistory::record(Auth::id(), $article->id, $chapter->id, $number);
            }
        }

        $comments = $article->getNewestCommentsPaginate();

        // Danh sách chương cho panel "mục lục" trong trang đọc — dùng chung partial với
        // tab chương ở trang chi tiết truyện nên cần unlockedChapterIds + hasActiveVip.
        $articleChapters = $article->chapters()->orderBy('number', 'desc')->get();
        $unlockedChapterIds = Auth::check()
            ? ChapterUnlock::where('user_id', Auth::id())
                ->whereIn('chapter_id', $articleChapters->pluck('id'))
                ->pluck('chapter_id')
            : collect();

        return view('client.chapters.show', [
            'article'        => $article,
            'chapter'        => $chapter,
            'articleChapters' => $articleChapters,
            'unlockedChapterIds' => $unlockedChapterIds,
            'hasActiveVip'   => $hasActiveVip,
            'user'           => $article->user,
            'comments'       => $comments,
            'showPopup'      => $showPopup,
            'requireClick'   => $requireClick,
            'affiLink'       => $popupAdItem?->link ?? $chapterAd?->link ?? '',
            'affiImage'      => $popupAdItem?->image ?? $chapterAd?->image ?? '',
            'affiScript'     => $popupAdItem?->script_code ?? $chapterAd?->script_code ?? '',
            'inlineChapterAds' => $inlineChapterAds,
            'inlineAdFirstAfter' => $inlineAdFirstAfter,
            'inlineAdEvery' => $inlineAdEvery,
            'isUserLoggedIn' => Auth::check(),
            'creditCost'     => $creditCost,
            'alreadyUnlocked' => $alreadyUnlocked,
            'isLocked'       => $isLocked,
            'userPoints'     => Auth::check() ? (int) Auth::user()->points : 0,
            'hasStartedReading' => $hasStartedReading,
            'currentListStatus' => $currentListStatus,
            'bookmarkParagraph' => $bookmarkParagraph,
            'chapterLikesCount' => $chapterLikesCount,
            'userLikedChapter' => $userLikedChapter,
        ]);
    }

    /**
     * "Give thanks" — like/unlike chương (toggle).
     */
    public function likeChapter(Request $request, Article $article, $number)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        $chapter = $article->chapters()->where('number', $number)->firstOrFail();

        $existing = ChapterLike::where('user_id', Auth::id())
            ->where('chapter_id', $chapter->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            ChapterLike::create(['user_id' => Auth::id(), 'chapter_id' => $chapter->id]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked'   => $liked,
            'count'   => ChapterLike::where('chapter_id', $chapter->id)->count(),
        ]);
    }

    /**
     * Lưu/xoá vị trí đoạn đang đọc dở của user trong chương (server-side bookmark).
     * paragraph > 0: lưu; paragraph = 0: xoá.
     */
    public function bookmarkParagraph(Request $request, Article $article, $number)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        $data = $request->validate([
            'paragraph' => ['required', 'integer', 'min:0'],
        ]);

        $chapter = $article->chapters()->where('number', $number)->firstOrFail();
        $paragraph = (int) $data['paragraph'];

        if ($paragraph <= 0) {
            ChapterParagraphBookmark::where('user_id', Auth::id())
                ->where('chapter_id', $chapter->id)
                ->delete();

            return response()->json(['success' => true, 'paragraph' => 0]);
        }

        ChapterParagraphBookmark::updateOrCreate(
            ['user_id' => Auth::id(), 'chapter_id' => $chapter->id],
            ['article_id' => $article->id, 'paragraph' => $paragraph]
        );

        return response()->json(['success' => true, 'paragraph' => $paragraph]);
    }

    /**
     * Ghi nhận báo cáo lỗi chương để admin xử lý.
     */
    public function report(Request $request, Article $article, $number)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $chapter = $article->chapters()->where('number', $number)->firstOrFail();

        ChapterReport::create([
            'chapter_id' => $chapter->id,
            'article_id' => $article->id,
            'user_id'    => Auth::id(),
            'reason'     => $data['reason'] ?? null,
            'resolved'   => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.chapter.report_sent'),
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
