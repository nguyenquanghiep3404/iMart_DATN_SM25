<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel; // Quan trọng: Sử dụng PrivateChannel cho kênh riêng tư
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage; // Đảm bảo import ChatMessage model
use App\Models\ChatConversation; // Đảm bảo import ChatConversation model

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation; // Truyền cả cuộc hội thoại nếu cần thông tin của nó ở frontend

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ChatMessage $message, ChatConversation $conversation)
    {
        // Load sender relationship để có thể truy cập message.sender.name ở frontend
        $this->message = $message->load('sender');
        $this->conversation = $conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Tin nhắn sẽ được phát sóng trên kênh riêng tư của cuộc hội thoại
        return [
            new PrivateChannel('chat.conversation.' . $this->conversation->id),
            // Tùy chọn: Phát sóng lên kênh cá nhân của người nhận nếu bạn muốn thông báo real-time
            // new PrivateChannel('users.' . $this->message->receiver_id), // Nếu có receiver_id trong message
        ];
    }

    /**
     * The event's broadcast name.
     * Laravel Echo sẽ lắng nghe event với tên này (ví dụ: .message.sent)
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->toArray(),
            'conversation' => $this->conversation->toArray(),
        ];
    }
}
