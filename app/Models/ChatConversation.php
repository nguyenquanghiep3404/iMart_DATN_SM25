<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    public function messages() {
    return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }
    public function participants() {
        return $this->belongsToMany(User::class, 'chat_participants');
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
