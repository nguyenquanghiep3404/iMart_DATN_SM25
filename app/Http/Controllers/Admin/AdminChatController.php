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

        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();
        // dd($admins->toArray());

        return view('admin.chat.dashboard', compact('openSupportConversations', 'internalConversations', 'admins'));
    }

    public function show(ChatConversation $conversation)
    {
        if (!$conversation->assigned_to) {
            $conversation->assigned_to = Auth::id();
            $conversation->save();
        }

        $conversation->load(['user', 'assignedTo', 'messages.sender', 'participants.user']);

        return response()->json(['conversation' => $conversation]);
    }

    public function sendMessage(Request $request, ChatConversation $conversation)
    {
        // dd($request->all());
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


        event(new NewMessageSent($message, $conversation));

        return response()->json(['message' => 'Message sent!']);
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

        $adminIds = collect($request->recipient_ids)->push(Auth::id())->unique()->toArray();

        $conversation = ChatConversation::create([
            'type' => 'internal',
            'user_id' => null,
            'assigned_to' => Auth::id(),
            'subject' => $request->subject ?? 'Internal Chat',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        foreach ($adminIds as $adminId) {
            ChatParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $adminId,
            ]);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => $request->first_message,
            'type' => 'text',
        ]);

        event(new NewConversationCreated($conversation, Auth::user())); // Người tạo
        foreach ($adminIds as $adminId) {
            if ($adminId != Auth::id()) {
                $recipient = User::find($adminId);
                if ($recipient) {
                    event(new NewConversationCreated($conversation, $recipient));
                }
            }
        }

        return response()->json(['message' => 'Internal chat created successfully!', 'conversation_id' => $conversation->id]);
    }
    public function adminChatDashboard()
{
    $admins = User::role('admin')->with('roles')->get(); // Lấy tất cả user có vai trò 'admin' và load luôn roles của họ
    // Hoặc nếu bạn chỉ muốn lấy những người có thể được mời:
    // $admins = User::permission('invite users')->with('roles')->get();

    // ... các logic khác ...

    return view('admin.chat.dashboard', [
        // ...
        'admins' => $admins->toArray(), // Đảm bảo roles được serialize vào array
        'authUser' => optional(Auth::user())->toArray(),
    ]);
}
}
