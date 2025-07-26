<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatConversation;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\User;

class NewConversationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;
    public $recipientUser; // Tùy chọn: cho các thông báo nhắm mục tiêu

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ChatConversation $conversation, User $recipientUser = null)
    {
        $this->conversation = $conversation->load(['user', 'assignedTo', 'participants.user']);
        $this->recipientUser = $recipientUser;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if ($this->conversation->type === 'support') {
            // Broadcast tới tất cả các quản trị viên (giả sử có kênh 'admins' cho các thông báo chung)
            return new Channel('admin.notifications');
        } elseif ($this->conversation->type === 'internal' && $this->recipientUser) {
            // Đối với các cuộc trò chuyện nội bộ, broadcast tới kênh riêng tư của một quản trị viên cụ thể
            return new PrivateChannel('users.' . $this->recipientUser->id);
        }
        return []; // Không nên xảy ra
    }

    /**
     * Tên broadcast của event.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'conversation.created';
    }
}
