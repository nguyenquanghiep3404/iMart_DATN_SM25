<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kênh riêng tư cho một cuộc hội thoại trò chuyện cụ thể
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    // Kiểm tra xem người dùng có phải là người tham gia cuộc hội thoại này không
    $isParticipant = \App\Models\ChatParticipant::where('conversation_id', $conversationId)
                                            ->where('user_id', $user->id)
                                            ->exists();
    return $isParticipant;
});

// Kênh công khai cho các thông báo của quản trị viên (các cuộc trò chuyện hỗ trợ mới)
// Bạn có thể muốn đặt nó là riêng tư và hạn chế quyền truy cập chỉ cho các quản trị viên đã xác thực
Broadcast::channel('admin.notifications', function ($user) {
    // Giả sử 'admin' là một tên vai trò trong bảng roles của bạn
    return $user->hasRole('admin');
});

// Kênh riêng tư cho các thông báo người dùng cụ thể (ví dụ: lời mời trò chuyện nội bộ mới)
Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
