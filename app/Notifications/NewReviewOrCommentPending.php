<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReviewOrCommentPending extends Notification
{
    use Queueable;

    public string $type;
    public string $targetTitle;
    public string $url;

    public function __construct(string $type, string $targetTitle, string $url)
    {
        $this->type = $type; // 'đánh giá' hoặc 'bình luận'
        $this->targetTitle = $targetTitle;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['database']; // web notification only
    }

    public function toArray($notifiable)
    {
        return [
            'title' => "Có một {$this->type} mới đang chờ duyệt",
            'message' => "Có một {$this->type} mới cho \"{$this->targetTitle}\" đang chờ được duyệt.",
            'icon' => 'check',
            'color' => 'green',
            'url' => $this->url,
        ];
    }
}
