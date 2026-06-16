<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Scopes\PublishedChapterScope;
use Illuminate\Console\Command;

class DispatchNewChapterNotifications extends Command
{
    protected $signature = 'notifications:new-chapters';
    protected $description = 'Gửi noti "chương mới" cho chương đã xuất bản mà chưa báo (gồm chương hẹn giờ vừa tới giờ).';

    public function handle(): int
    {
        $chapters = Chapter::withoutGlobalScope(PublishedChapterScope::class)
            ->whereNull('notified_at')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderBy('id')
            ->limit(200)
            ->get();

        foreach ($chapters as $chapter) {
            $chapter->dispatchNewChapterNotification();
        }

        $this->info("Dispatched notifications for {$chapters->count()} chapter(s).");
        return self::SUCCESS;
    }
}
