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
use Illuminate\Support\Facades\Gate; // Thêm Gate để kiểm tra quyền

class AdminChatController extends Controller
{
    // public function __construct()
    // {
    //     // Áp dụng cho toàn bộ controller, hoặc bạn có thể chỉ định riêng
    //     // $this->middleware('can:manage_chat');
    // }

    public function index()
    {
        $openSupportConversations = ChatConversation::where('type', 'support')
            ->where('status', 'open')
            ->with(['user', 'assignedTo', 'latestMessage']) // SỬA: Chống N+1 Query
            ->orderByDesc('last_message_at')
            ->get();

        $internalConversations = ChatConversation::where('type', 'internal')
            ->where('status', 'open')
            ->with(['participants.user', 'latestMessage']) // SỬA: Chống N+1 Query
            ->orderByDesc('last_message_at')
            ->get();

        // SỬA: Lấy danh sách admin một cách gọn gàng
        $admins = User::whereHas('roles', function ($query) {
    $query->where('name', 'admin');
})->get();


        return view('admin.chat.dashboard', compact('openSupportConversations', 'internalConversations', 'admins'));
    }

    public function show(ChatConversation $conversation)
{
    $adminId = Auth::id();

    if ($conversation->type === 'support' && !$conversation->assigned_to) {
        $conversation->update(['assigned_to' => $adminId]);

        // ✅ QUAN TRỌNG: Đảm bảo dòng này đã được thêm vào
        $conversation->refresh();
    }
    
    if ($conversation->assigned_to === $adminId) {
         ChatParticipant::firstOrCreate([
            'conversation_id' => $conversation->id,
            'user_id' => $adminId,
        ]);
    }

    $conversation->load(['user', 'assignedTo', 'messages.sender', 'participants.user']);

    return response()->json(['conversation' => $conversation]);
}



    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        // SỬA: Bắt buộc kiểm tra quyền tham gia
        if (Gate::denies('participate', $conversation)) {
            return response()->json(['message' => 'Unauthorized to send message in this conversation.'], 403);
        }
        
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
        
        $message->load('sender'); // Load trước để gửi đi payload đầy đủ

        broadcast(new NewMessageSent($message, $conversation))->toOthers();

        return response()->json(['message' => 'Message sent!', 'data' => ['message' => $message]]);
    }

    public function inviteAdmin(Request $request, ChatConversation $conversation)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
        ]);

        $admin = User::find($request->admin_id);

        if (!$admin || !$admin->hasRole('admin')) {
            return response()->json(['message' => 'Invalid admin selected.'], 403);
        }

        $exists = ChatParticipant::where('conversation_id', $conversation->id)
                                  ->where('user_id', $admin->id)
                                  ->exists();

        if ($exists) {
            return response()->json(['message' => 'Admin is already a participant.'], 409);
        }

        ChatParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $admin->id,
        ]);

        return response()->json(['message' => 'Admin invited to conversation.']);
    }

    public function close(ChatConversation $conversation)
    {
        $conversation->update(['status' => 'closed']);
        return response()->json(['message' => 'Conversation closed.']);
    }

    public function createInternalChat(Request $request)
    {
        $request->validate([
            'recipient_ids' => 'required|array',
            'recipient_ids.*' => 'exists:users,id',
            'first_message' => 'required|string',
            'subject' => 'nullable|string|max:255',
        ]);

        $adminIds = collect($request->recipient_ids)->push(Auth::id())->unique()->all();

        // Sử dụng transaction để đảm bảo toàn vẹn dữ liệu
        $conversation = \DB::transaction(function () use ($request, $adminIds) {
            $conversation = ChatConversation::create([
                'type' => 'internal',
                'user_id' => null, // Không phải từ khách hàng
                'assigned_to' => Auth::id(), // Người tạo
                'subject' => $request->subject ?: 'Internal Chat',
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            $participantsData = array_map(fn($id) => ['user_id' => $id, 'conversation_id' => $conversation->id], $adminIds);
            ChatParticipant::insert($participantsData);

            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'content' => $request->first_message,
                'type' => 'text',
            ]);
            
            return $conversation;
        });

        // SỬA: Chỉ broadcast 1 lần
        event(new NewConversationCreated($conversation->load('participants.user', 'latestMessage')));

        return response()->json(['message' => 'Internal chat created successfully!', 'conversation' => $conversation]);
    }
}