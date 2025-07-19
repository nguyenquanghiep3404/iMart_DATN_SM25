<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Kiểm tra xem user hiện tại có phải là người tham gia cuộc hội thoại không
    return \App\Models\ChatConversation::find($conversationId)
        ->participants()
        ->where('user_id', $user->id)
        ->exists();
});
