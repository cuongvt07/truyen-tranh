<?php

namespace App\Notifications;

use App\Models\Article;
use App\Models\Chapter;
use Illuminate\Notifications\Notification;

class NewChapterNotification extends Notification
{
    public function __construct(public Chapter $chapter)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $article = Article::withoutGlobalScopes()->find($this->chapter->article_id);
        $scheduled = $this->chapter->isScheduled();

        return [
            'type'           => $scheduled ? 'chapter_soon' : 'new_chapter',
            'mode'           => $scheduled ? 'soon' : 'new',
            'article_id'     => $this->chapter->article_id,
            'article_title'  => $article?->title,
            'article_slug'   => $article?->getRouteKey(),
            'chapter_id'     => $this->chapter->id,
            'chapter_number' => $this->chapter->number,
            'publish_at'     => $scheduled ? optional($this->chapter->published_at)->format('d/m/Y H:i') : null,
        ];
    }
}
