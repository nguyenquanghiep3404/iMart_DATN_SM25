<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\ChatConversation;

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ChatMessage $message, ChatConversation $conversation)
    {
        $this->message = $message->load('sender'); // Tải người gửi để bao gồm trong broadcast
        $this->conversation = $conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast trên một kênh riêng tư cho cuộc hội thoại cụ thể
        // Điều này đảm bảo chỉ những người tham gia cuộc hội thoại mới nhận được tin nhắn
        return new PrivateChannel('chat.conversation.' . $this->conversation->id);
    }

    /**
     * Tên broadcast của event.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }
}
