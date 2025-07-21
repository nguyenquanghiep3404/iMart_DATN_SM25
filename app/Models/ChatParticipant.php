<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatParticipant extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    // Định nghĩa các mối quan hệ
    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
