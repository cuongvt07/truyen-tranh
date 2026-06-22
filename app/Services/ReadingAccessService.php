<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class ReadingAccessService
{
    private const GUEST_SESSION_PREFIX = 'guest_reading_limits.';
    private array $resolvedLimits = [];

    public function __construct(private ?array $limits = null)
    {
    }

    public function guestCanRead(Request $request, int $articleId, int $chapterId): bool
    {
        $usage = $this->guestUsage($request);

        if (in_array($chapterId, $usage['chapters'], true)) {
            return true;
        }

        if (count($usage['chapters']) >= $this->guestChapterLimit()) {
            return false;
        }

        return in_array($articleId, $usage['articles'], true)
            || count($usage['articles']) < $this->guestArticleLimit();
    }

    public function recordGuestRead(Request $request, int $articleId, int $chapterId): void
    {
        $usage = $this->guestUsage($request);
        $usage['articles'][] = $articleId;
        $usage['chapters'][] = $chapterId;
        $usage['articles'] = array_values(array_unique($usage['articles']));
        $usage['chapters'] = array_values(array_unique($usage['chapters']));

        $request->session()->put($this->guestSessionKey(), $usage);
    }

    public function unpaidUserCanRead(User $user, int $chapterId): bool
    {
        if ($user->readingHistories()->where('chapter_id', $chapterId)->exists()) {
            return true;
        }

        if ($user->deposits()->where('status', 'completed')->exists()) {
            return true;
        }

        return $user->readingHistories()->count()
            < $this->unpaidUserChapterLimit();
    }

    public function guestArticleLimit(): int
    {
        return $this->limit('guest_articles_per_day', 5);
    }

    public function guestChapterLimit(): int
    {
        return $this->limit('guest_chapters_per_day', 10);
    }

    public function unpaidUserChapterLimit(): int
    {
        return $this->limit('unpaid_user_chapters', 100);
    }

    private function guestUsage(Request $request): array
    {
        $usage = $request->session()->get($this->guestSessionKey(), []);

        return [
            'articles' => array_map('intval', $usage['articles'] ?? []),
            'chapters' => array_map('intval', $usage['chapters'] ?? []),
        ];
    }

    private function guestSessionKey(): string
    {
        return self::GUEST_SESSION_PREFIX.now()->toDateString();
    }

    private function limit(string $key, int $default): int
    {
        if (array_key_exists($key, $this->resolvedLimits)) {
            return $this->resolvedLimits[$key];
        }

        if (array_key_exists($key, $this->limits ?? [])) {
            return $this->resolvedLimits[$key] = max(1, (int) $this->limits[$key]);
        }

        return $this->resolvedLimits[$key] = max(1, (int) setting(
            $key,
            config("reading_limits.{$key}", $default)
        ));
    }
}
