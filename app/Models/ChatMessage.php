<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    public function conversation() {
    return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
