<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ChapterLike;
use App\Models\CommentVote;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\User;
use App\Models\UserAchievement;

class AchievementService
{
    /** Metrics cần tính cho user. */
    public static function metrics(User $user): array
    {
        $uid = $user->id;
        return [
            // Đọc
            'chapters_read'      => $user->readingHistories()->count(),
            'articles_read'      => $user->readingHistories()->distinct('article_id')->count('article_id'),
            // Tương tác
            'comments_posted'    => $user->comments()->count(),
            'chapter_likes'      => ChapterLike::where('user_id', $uid)->count(),
            'comment_votes'      => CommentVote::where('user_id', $uid)->count(),
            // Chương mở khoá
            'chapters_unlocked'  => $user->chapterUnlocks()->count(),
            'credits_spent'      => (int) $user->chapterUnlocks()->sum('credits_spent'),
            // Theo dõi
            'bookmarks'          => $user->bookmarks()->count(),
            // Diễn đàn
            'forum_posts'        => ForumPost::where('user_id', $uid)->where('status', 'approved')->count(),
            'forum_comments'     => ForumComment::where('user_id', $uid)->count(),
            // Nạp tiền
            'deposit_count'      => $user->deposits()->where('status', 'completed')->count(),
            'deposit_total'      => (int) $user->deposits()->where('status', 'completed')->sum('amount'),
        ];
    }

    /** Check và mở khoá achievements mới, trả về danh sách vừa mở khoá. */
    public static function sync(User $user): array
    {
        $metrics  = static::metrics($user);
        $earned   = UserAchievement::where('user_id', $user->id)->pluck('achievement_id')->flip();
        $unlocked = [];

        foreach (Achievement::all() as $ach) {
            if ($earned->has($ach->id)) continue;
            if (($metrics[$ach->metric] ?? 0) >= $ach->target) {
                UserAchievement::create([
                    'user_id'        => $user->id,
                    'achievement_id' => $ach->id,
                    'unlocked_at'    => now(),
                ]);
                // Thưởng credits
                if ($ach->reward_credits > 0) {
                    $user->increment('points', $ach->reward_credits);
                }
                $unlocked[] = $ach;
            }
        }

        return $unlocked;
    }

    /** Lấy dữ liệu đầy đủ cho view: tất cả achievements + tiến độ + trạng thái. */
    public static function forUser(User $user): array
    {
        $metrics  = static::metrics($user);
        $earnedAt = UserAchievement::where('user_id', $user->id)
            ->pluck('unlocked_at', 'achievement_id');

        $all = Achievement::orderBy('sort_order')->get()->map(function ($ach) use ($metrics, $earnedAt) {
            $current  = $metrics[$ach->metric] ?? 0;
            $progress = $ach->target > 0 ? min(100, (int) ($current / $ach->target * 100)) : 100;
            return [
                'achievement'  => $ach,
                'earned'       => $earnedAt->has($ach->id),
                'unlocked_at'  => $earnedAt->get($ach->id),
                'current'      => $current,
                'progress'     => $progress,
            ];
        });

        return [
            'all'      => $all,
            'earned'   => $all->where('earned', true),
            'missions' => $all->where('earned', false),
            'metrics'  => $metrics,
        ];
    }
}
