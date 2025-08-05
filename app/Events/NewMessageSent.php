<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel; // Quan trọng: Đảm bảo sử dụng PrivateChannel
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage;
use App\Models\ChatConversation;

class NewMessageSent implements ShouldBroadcastNow 
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
    $channels = [];

    // 1. Kênh chung cho cuộc hội thoại (để tất cả mọi người trong đó thấy tin nhắn)
    $channels[] = new PrivateChannel('chat.conversation.' . $this->conversation->id);

    // 2. Gửi đến kênh riêng của từng admin để cập nhật UI danh sách hội thoại
    //    ngay cả khi họ không mở cuộc hội thoại đó.
    $adminParticipants = $this->conversation->participants()
        ->whereHas('user.roles', function ($query) {
            $query->whereIn('name', ['admin', 'super_admin']);
        })
        ->get();

    foreach ($adminParticipants as $participant) {
        // Không cần gửi thông báo riêng cho chính người vừa gửi tin nhắn
        if ($this->message->sender_id !== $participant->user_id) {
            $channels[] = new PrivateChannel('users.' . $participant->user_id);
        }
    }

    return $channels;
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
