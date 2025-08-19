<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatConversation;

class NewConversationCreated implements ShouldBroadcastNow 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatConversation $conversation;

    /**
     * Create a new event instance.
     */
    public function __construct(ChatConversation $conversation)
    {
        // Tải trước các dữ liệu cần thiết để gửi đi trong payload
        $this->conversation = $conversation->load(['user', 'latestMessage', 'participants.user']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // 1. Nếu là chat HỖ TRỢ, gửi đến kênh PUBLIC cho tất cả admin
        if ($this->conversation->type === 'support') {
            $channels[] = new Channel('admin.notifications');
        }

        // 2. Nếu là chat NỘI BỘ, gửi đến kênh PRIVATE của từng người tham gia
        if ($this->conversation->type === 'internal') {
            // Tải lại quan hệ participants nếu chưa có
            $this->conversation->loadMissing('participants'); 
            
            foreach ($this->conversation->participants as $participant) {
                $channels[] = new PrivateChannel('users.' . $participant->user_id);
            }
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.created';
    }
}