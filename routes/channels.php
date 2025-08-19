<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatConversation;
use App\Models\ChatParticipant;
use Illuminate\Support\Facades\Log; // Thêm dòng này để sử dụng Log


// Kênh riêng tư cho cuộc hội thoại chat
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    // Nếu không có user đăng nhập qua session, thử tìm user khách qua cookie
    if (!$user) {
        $guestUserId = request()->cookie('guest_user_id');
        if ($guestUserId) {
            // Tìm user trong DB, đảm bảo đó là tài khoản khách
            $user = User::where('id', $guestUserId)->where('is_guest', true)->first();
        }
    }

    // Nếu sau khi kiểm tra cả session và cookie mà vẫn không có user, từ chối quyền
    if (!$user) {
        return false;
    }

    // Lấy thông tin cuộc hội thoại
    $conversation = ChatConversation::find($conversationId);
    if (!$conversation) {
        return false;
    }

    // Kiểm tra xem user có phải là người tham gia hợp lệ của cuộc hội thoại không
    $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
    
    // Đối với support chat, khách hàng (chủ của cuộc hội thoại) luôn có quyền
    $isOwner = ($conversation->type === 'support' && $conversation->user_id === $user->id);

    return $isParticipant || $isOwner;
});


// Kênh riêng tư cho từng người dùng
Broadcast::channel('users.{userId}', function ($user, $userId) {
    if (!$user) {
        return false;
    }
    Log::info("Channel Auth: Private User Channel. User ID: {$user->id}, Target User ID: {$userId}. Authorized: " . var_export(((int)$user->id === (int)$userId), true));
    return (int) $user->id === (int) $userId;
});

// Kênh công khai hoặc thông báo chung cho admin
Broadcast::channel('admin.notifications', function ($user) {
    if (!$user) {
        return false;
    }
    $hasAdminOrSupportRole = $user->hasAnyRole(['admin', 'support_staff']);
    Log::info("Channel Auth: Admin Notifications. User ID: {$user->id}, Has Admin/Support Role: " . var_export($hasAdminOrSupportRole, true) . ". Authorized: " . var_export($hasAdminOrSupportRole, true));
    return $hasAdminOrSupportRole;
});
