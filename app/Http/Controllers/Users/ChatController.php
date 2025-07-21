<?php



namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Events\NewMessageSent; // Bạn sẽ tạo event này
use App\Events\NewConversationCreated; // Bạn sẽ tạo event này

class ChatController extends Controller
{
    // Hiển thị giao diện chat chính cho người dùng
    public function index(Request $request)
    {
        $user = Auth::user();
        $guestUserId = $request->cookie('guest_user_id'); // Lấy guest_user_id từ cookie

        if ($user) {
            // Trường hợp 1: Người dùng đã đăng nhập
            $conversations = ChatConversation::where('user_id', $user->id)
                                                ->orWhereHas('participants', function ($query) use ($user) {
                                                    $query->where('user_id', $user->id);
                                                })
                                                ->orderByDesc('last_message_at')
                                                ->get();
            $greeting = "Chào anh " . $user->name . "..."; // Lời chào cá nhân hóa
        } elseif ($guestUserId) {
            // Trường hợp 2: Khách vãng lai có ID hiện có
            $guestUser = User::where('id', $guestUserId)->where('is_guest', true)->first();
            if ($guestUser) {
                $conversations = ChatConversation::where('user_id', $guestUser->id)
                                                    ->orderByDesc('last_message_at')
                                                    ->get();
                $greeting = "Chào khách vãng lai #" . $guestUser->id . "..."; // Hoặc một lời chào chung
            } else {
                // ID khách không hợp lệ, xóa nó và coi như khách lần đầu tiên
                $guestUserId = null;
                $conversations = collect();
                $greeting = null;
            }
        } else {
            // Trường hợp 2: Khách lần đầu tiên
            $conversations = collect();
            $greeting = null;
        }

        return view('users.partials.ai_chatbot', compact('conversations', 'greeting', 'guestUserId'));
    }

    // Xử lý đăng ký người dùng khách (khi họ bắt đầu chat lần đầu)
    public function registerGuest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
        ]);

        $guestUser = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => Str::uuid() . '@guest.com', // Email giả cho khách
            'password' => bcrypt(Str::random(16)), // Mật khẩu giả
            'is_guest' => true, // Đánh dấu là khách
            'status' => 'active', // Trạng thái mặc định
        ]);

        // Lưu guest_user_id vào localStorage thông qua cookie để duy trì
        // Đây là cách phổ biến để mô phỏng tương tác localStorage từ backend trong Laravel
        return response()->json([
            'message' => 'Guest registered successfully.',
            'user_id' => $guestUser->id
        ])->cookie('guest_user_id', $guestUser->id, 60*24*30); // Cookie 30 ngày
    }

    // Gửi tin nhắn
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:chat_conversations,id',
            'content' => 'required|string',
            'type' => 'in:text,image,file,system',
            'guest_user_id' => 'nullable|exists:users,id,is_guest,1', // Xác thực nếu là ID khách
        ]);

        $sender = Auth::user();
        $conversation = null;

        if (!$sender && $request->guest_user_id) {
            $sender = User::find($request->guest_user_id);
            if (!$sender || !$sender->is_guest) {
                return response()->json(['message' => 'Invalid sender.'], 403);
            }
        } elseif (!$sender) {
            return response()->json(['message' => 'Authentication required or guest ID missing.'], 401);
        }

        // Nếu không có conversation_id, tạo một cuộc hội thoại hỗ trợ mới
        if (!$request->conversation_id) {
            $conversation = ChatConversation::create([
                'type' => 'support',
                'user_id' => $sender->id,
                'status' => 'open',
                'last_message_at' => now(),
            ]);
            ChatParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $sender->id,
            ]);
            // Phát sóng cuộc hội thoại mới tới các quản trị viên
            event(new NewConversationCreated($conversation));
        } else {
            $conversation = ChatConversation::find($request->conversation_id);
            if (!$conversation) {
                return response()->json(['message' => 'Conversation not found.'], 404);
            }
            // Cập nhật dấu thời gian tin nhắn cuối cùng
            $conversation->update(['last_message_at' => now()]);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
        ]);

        // Phát sóng tin nhắn mới
        event(new NewMessageSent($message, $conversation));

        return response()->json(['message' => 'Message sent!', 'conversation_id' => $conversation->id]);
    }

    // Xử lý khi khách đăng nhập (hợp nhất tài khoản)
    public function guestLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'guest_user_id' => 'required|exists:users,id,is_guest,1',
        ]);

        $guestUser = User::find($request->guest_user_id);
        if (!$guestUser) {
            return response()->json(['message' => 'Guest user not found.'], 404);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $realUser = Auth::user();

            // Hợp nhất dữ liệu: Cập nhật user_id trong các bảng liên quan
            ChatConversation::where('user_id', $guestUser->id)->update(['user_id' => $realUser->id]);
            ChatMessage::where('sender_id', $guestUser->id)->update(['sender_id' => $realUser->id]);
            ChatParticipant::where('user_id', $guestUser->id)->update(['user_id' => $realUser->id]);

            // Xóa tài khoản khách
            $guestUser->delete();

            // Xóa cookie guest_user_id từ frontend
            return response()->json(['message' => 'Login successful, accounts merged.'])
                             ->withoutCookie('guest_user_id');
        }

        return response()->json(['message' => 'Invalid credentials.'], 401);
    }
}
