<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Events\NewMessageSent;
use App\Events\NewConversationCreated;

class AdminChatController extends Controller
{
        public function __construct()
    {
        $this->middleware('can:manage_chat');
    }
    // Hiển thị bảng điều khiển trò chuyện của quản trị viên
    public function index()
    {
        $openSupportConversations = ChatConversation::where('type', 'support')
                                                    ->where('status', 'open')
                                                    ->with(['user', 'assignedTo', 'messages'])
                                                    ->orderByDesc('last_message_at')
                                                    ->get();

        $internalConversations = ChatConversation::where('type', 'internal')
                                                ->where('status', 'open')
                                                ->with(['participants.user', 'messages'])
                                                ->orderByDesc('last_message_at')
                                                ->get();

        return view('admin.chat.dashboard', compact('openSupportConversations', 'internalConversations'));
    }

    // Xem một cuộc hội thoại trò chuyện cụ thể và gán nó cho quản trị viên
    public function show(ChatConversation $conversation)
    {
        // Tự động gán cuộc hội thoại nếu chưa được gán
        if (!$conversation->assigned_to) {
            $conversation->assigned_to = Auth::id();
            $conversation->save();
        }

        $conversation->load(['user', 'assignedTo', 'messages.sender', 'participants.user']);
        $admins = User::whereHas('roles', function ($query) { // Giả sử có các vai trò cho quản trị viên
            $query->where('name', 'admin'); // Hoặc tên vai trò quản trị viên cụ thể
        })->get();

        return view('admin.chat.conversation', compact('conversation', 'admins'));
    }

    // Gửi tin nhắn từ quản trị viên
    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'in:text,image,file,system',
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => $request->content,
            'type' => $request->type ?? 'text',
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Phát sóng tin nhắn mới
        event(new NewMessageSent($message, $conversation));

        return response()->json(['message' => 'Message sent!']);
    }

    // Mời quản trị viên khác vào một cuộc trò chuyện
    public function inviteAdmin(Request $request, ChatConversation $conversation)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id', // Đảm bảo người dùng được mời tồn tại
        ]);

        $admin = User::find($request->admin_id);

        if (!$admin || !$admin->hasRole('admin')) { // Giả sử vai trò 'admin'
            return response()->json(['message' => 'Invalid admin selected.'], 403);
        }

        // Thêm người tham gia nếu chưa có
        ChatParticipant::firstOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $admin->id,
        ]);

        // Tùy chọn, phát sóng một sự kiện để thông báo cho quản trị viên được mời
        // event(new AdminInvitedToConversation($conversation, $admin));

        return response()->json(['message' => 'Admin invited to conversation.']);
    }

    // Đóng một cuộc hội thoại trò chuyện
    public function closeConversation(ChatConversation $conversation)
    {
        $conversation->update(['status' => 'closed']);
        return response()->json(['message' => 'Conversation closed.']);
    }

    // Tạo cuộc trò chuyện nội bộ (giữa các quản trị viên)
    public function createInternalChat(Request $request)
    {
        $request->validate([
            'recipient_ids' => 'required|array',
            'recipient_ids.*' => 'exists:users,id',
            'first_message' => 'required|string',
            'subject' => 'nullable|string|max:255',
        ]);

        $adminIds = collect($request->recipient_ids)->push(Auth::id())->unique()->toArray();

        // Tạo cuộc hội thoại mới
        $conversation = ChatConversation::create([
            'type' => 'internal',
            'user_id' => null, // Không có người dùng khách hàng
            'assigned_to' => Auth::id(), // Gán cho người tạo
            'subject' => $request->subject ?? 'Internal Chat', // Chủ đề tùy chọn
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        // Thêm người tham gia
        foreach ($adminIds as $adminId) {
            ChatParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $adminId,
            ]);
        }

        // Lưu tin nhắn đầu tiên
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => $request->first_message,
            'type' => 'text',
        ]);

        // Phát sóng cho tất cả những người tham gia rằng một cuộc hội thoại nội bộ mới đã được tạo
        foreach ($adminIds as $adminId) {
            $recipient = User::find($adminId);
            if ($recipient) {
                event(new NewConversationCreated($conversation, $recipient));
            }
        }

        return response()->json(['message' => 'Internal chat created successfully!', 'conversation_id' => $conversation->id]);
    }
}
