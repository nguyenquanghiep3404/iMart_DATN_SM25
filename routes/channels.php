<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatConversation;
use App\Models\ChatParticipant;
use Illuminate\Support\Facades\Log; // Thêm dòng này để sử dụng Log

// Kênh riêng tư cho cuộc hội thoại chat
Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    // --- BẮT ĐẦU GỠ LỖI ---
    // Ghi lại userId và conversationId để đảm bảo chúng chính xác
    Log::info("Channel Auth: User ID - " . ($user ? $user->id : 'NULL') . ", Conv ID - " . $conversationId);

    if (!$user) {
        Log::warning("Channel Auth: User not authenticated for conversation " . $conversationId);
        return false; // Người dùng chưa xác thực
    }

    // Thử trả về true trực tiếp để xem lỗi JSON có còn không
    // Nếu điều này hoạt động, vấn đề nằm trong logic ủy quyền chi tiết của bạn bên dưới.
    // return true; // <-- BỎ GHI CHÚ DÒNG NÀY ĐỂ KIỂM TRA NHANH

    // Nếu `return true;` ở trên KHÔNG khắc phục được lỗi JSON,
    // vấn đề có thể nằm ngoài closure này, ví dụ: một lệnh `echo` ở nơi khác.

    $conversation = ChatConversation::find($conversationId);

    if (!$conversation) {
        Log::warning("Channel Auth: Conversation " . $conversationId . " not found.");
        return false; // Không tìm thấy cuộc hội thoại
    }

    $isAuthorized = false; // Mặc định là false

    if ($conversation->type === 'support') {
        $hasAdminRole = $user->hasAnyRole(['admin', 'support_staff']);
        $isCustomer = ($conversation->user_id === (int)$user->id);
        $isAuthorized = $hasAdminRole || $isCustomer;
        Log::info("Channel Auth: Support chat. User is admin/support: " . var_export($hasAdminRole, true) . ", User is customer: " . var_export($isCustomer, true) . ". Authorized: " . var_export($isAuthorized, true));
    } elseif ($conversation->type === 'internal') {
        $isParticipant = $conversation->participants->contains('user_id', (int)$user->id);
        $isAuthorized = $isParticipant;
        Log::info("Channel Auth: Internal chat. User is participant: " . var_export($isParticipant, true) . ". Authorized: " . var_export($isAuthorized, true));
    } else {
        Log::info("Channel Auth: Unknown conversation type for " . $conversationId);
    }

    return $isAuthorized;
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
