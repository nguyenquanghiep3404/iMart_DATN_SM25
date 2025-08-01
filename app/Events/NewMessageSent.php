<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel; // Quan trọng: Đảm bảo sử dụng PrivateChannel
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage;
use App\Models\ChatConversation;

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;

    public function __construct(ChatMessage $message, ChatConversation $conversation)
    {
        $this->message = $message;
        $this->conversation = $conversation;
    }

    public function broadcastOn(): array
    {
        // Kênh riêng tư cho cuộc hội thoại này.
        // Tên kênh phải khớp với tên lắng nghe của Echo.
        return [
            new PrivateChannel('chat.conversation.' . $this->conversation->id),
        ];
    }

    public function broadcastAs(): string
    {
        // Tên sự kiện mà client sẽ lắng nghe.
        // Phải khớp với .listen('.message.sent', ...)
        return 'message.sent';
    }

    // Tùy chọn: Chuẩn bị dữ liệu để broadcast
    public function broadcastWith(): array
    {
        // Eager load sender để client có thông tin người gửi
        $this->message->load('sender');
        // Trả về cả message và conversation (ví dụ như bạn đã làm trong show/edit của admin)
        return [
            'message' => $this->message->toArray(),
            'conversation' => $this->conversation->toArray(),
        ];
    }
}
