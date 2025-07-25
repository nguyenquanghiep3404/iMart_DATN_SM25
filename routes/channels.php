<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatConversation; // Đảm bảo import
use App\Models\ChatParticipant; // Đảm bảo import

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Kênh riêng tư cho cuộc hội thoại chat
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    // $user là người dùng đã đăng nhập (admin hiện tại)
    // Lấy cuộc hội thoại
    $conversation = ChatConversation::find($conversationId);

    if (!$conversation) {
        return false; // Cuộc hội thoại không tồn tại
    }

    // --- Logic xác thực cho Admin ---
    // 1. Nếu admin là người được gán cuộc hội thoại hỗ trợ
    if ($conversation->type === 'support' && $conversation->assigned_to === $user->id) {
        return true;
    }

    // 2. Nếu admin là người tham gia cuộc hội thoại nội bộ
    if ($conversation->type === 'internal') {
        // Kiểm tra xem $user có trong danh sách participants của cuộc hội thoại không
        return ChatParticipant::where('conversation_id', $conversationId)
                              ->where('user_id', $user->id)
                              ->exists();
    }

    // 3. Nếu admin có quyền 'manage_chat' tổng thể
    // Đây là cách phổ biến và đơn giản nhất để admin có thể xem tất cả chat
    if ($user->can('manage_chat')) { // Hoặc permission khác như 'access_admin_chat'
        return true;
    }

    // Nếu không thỏa mãn bất kỳ điều kiện nào, không có quyền
    return false;
});

// Kênh riêng tư cho từng người dùng (dùng cho thông báo cá nhân, ví dụ: có chat mới được giao)
Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Kênh công khai hoặc thông báo chung cho admin
Broadcast::channel('admin.notifications', function ($user) {
    // Chỉ cho phép người dùng có quyền 'manage_chat' (hoặc admin role) lắng nghe kênh này
    return $user->can('manage_chat'); // Hoặc $user->hasRole('admin');
});
