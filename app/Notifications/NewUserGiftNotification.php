<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class NewUserGiftNotification extends Notification
{
    public function __construct(public int $amount, public ?string $url = null)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'    => 'gift',
            'title'   => "Gift for New User: {$this->amount} LuneCoin",
            'url'     => $this->url ?: url('/catalog'),
            'icon'    => 'fa-gift',
        ];
    }
}
