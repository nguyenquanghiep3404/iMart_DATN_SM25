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
use App\Events\NewMessageSent;
use App\Events\NewConversationCreated;
use Illuminate\Http\Response; // Import Response để đặt cookie
use Illuminate\Validation\Rule; // Import Rule cho validation

class ChatController extends Controller
{
    // Hiển thị giao diện chat chính cho người dùng
    public function index(Request $request)
    {
        $conversations = collect(); // Khởi tạo Collection rỗng
        $guestUserId = null; // ID của guest user hiện tại (nếu là guest)
        $initialConversation = null; // Cuộc hội thoại ban đầu để hiển thị

        if (Auth::check()) {
            // Người dùng đã đăng nhập
            $currentUserId = Auth::id();
            // Lấy cuộc hội thoại hỗ trợ gần nhất của user này
            $initialConversation = ChatConversation::with('messages')
                                    ->where('user_id', $currentUserId)
                                    ->where('type', 'support')
                                    ->orderByDesc('updated_at')
                                    ->first();
            if ($initialConversation) {
                $conversations->push($initialConversation);
            } else {
                // Tạo cuộc hội thoại mới nếu chưa có
                $newConversation = ChatConversation::create([
                    'type' => 'support',
                    'user_id' => $currentUserId,
                    'status' => 'open',
                    'last_message_at' => now(),
                ]);
                ChatParticipant::create([
                    'conversation_id' => $newConversation->id,
                    'user_id' => $currentUserId,
                ]);
                $conversations->push($newConversation);
                event(new NewConversationCreated($newConversation));
                $initialConversation = $newConversation; // Gán để truyền xuống JS
            }
        } else {
            // Xử lý khách vãng lai
            $guestUserIdFromCookie = $request->cookie('guest_user_id'); // Lấy từ cookie
            $guestUser = null;

            if ($guestUserIdFromCookie) {
                $guestUser = User::where('id', $guestUserIdFromCookie)->where('is_guest', true)->first();
            }

            if ($guestUser) {
                $guestUserId = $guestUser->id; // Gán ID guest hợp lệ
                // Tìm cuộc hội thoại hỗ trợ gần nhất của guest này
                $initialConversation = ChatConversation::with('messages')
                                        ->where('user_id', $guestUser->id)
                                        ->where('type', 'support')
                                        ->orderByDesc('updated_at')
                                        ->first();
                if ($initialConversation) {
                    $conversations->push($initialConversation);
                } else {
                    // Tạo cuộc hội thoại mới nếu guest có ID nhưng chưa có conversation
                    $newConversation = ChatConversation::create([
                        'type' => 'support',
                        'user_id' => $guestUser->id,
                        'status' => 'open',
                        'last_message_at' => now(),
                    ]);
                    ChatParticipant::create([
                        'conversation_id' => $newConversation->id,
                        'user_id' => $guestUser->id,
                    ]);
                    $conversations->push($newConversation);
                    event(new NewConversationCreated($newConversation));
                    $initialConversation = $newConversation; // Gán để truyền xuống JS
                }
            }
            // Nếu không tìm thấy guestUser hợp lệ, $guestUserId vẫn là null, welcomeScreen sẽ hiện
        }

        // Truyền biến cho Blade view
        return view('users.partials.ai_chatbot', compact('conversations', 'guestUserId'));
    }

    // Xử lý đăng ký người dùng khách (khi họ bắt đầu chat lần đầu)
    // THAY ĐỔI: CÁCH 3 - Tìm guest cũ theo SĐT, nếu có thì tải lại conversation
    public function registerGuest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20', // Bỏ rule 'unique' ở đây
        ]);

        // Tìm kiếm khách vãng lai hiện có với số điện thoại này
        $guestUser = User::where('phone_number', $request->phone_number)
                         ->where('is_guest', true) // Chỉ tìm user là guest
                         ->first();

        $conversation = null;
        $isNewGuest = false;

        if ($guestUser) {
            // Nếu tìm thấy khách vãng lai cũ, cập nhật tên nếu có thay đổi
            $guestUser->update(['name' => $request->name]);
            // Tìm cuộc hội thoại gần nhất của guest này
            $conversation = ChatConversation::with('messages') // eager load messages để trả về frontend
                                ->where('user_id', $guestUser->id)
                                ->where('type', 'support')
                                ->orderByDesc('updated_at')
                                ->first();
            if (!$conversation) {
                // Nếu guest cũ nhưng chưa có conversation nào (hoặc đã bị xóa), tạo mới
                $conversation = ChatConversation::create([
                    'type' => 'support',
                    'user_id' => $guestUser->id,
                    'status' => 'open',
                    'last_message_at' => now(),
                ]);
                ChatParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $guestUser->id,
                ]);
                event(new NewConversationCreated($conversation));
            }
        } else {
            // Nếu không tìm thấy, tạo khách vãng lai mới
            $isNewGuest = true;
            $guestUser = User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => 'guest_' . Str::uuid() . '@example.com', // Email giả duy nhất
                'password' => bcrypt(Str::random(16)), // Mật khẩu giả
                'is_guest' => true, // Đánh dấu là khách
                'status' => 'active', // Trạng thái mặc định
            ]);

            // Tạo một cuộc hội thoại ban đầu ngay khi khách đăng ký
            $conversation = ChatConversation::create([
                'type' => 'support',
                'user_id' => $guestUser->id,
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            ChatParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $guestUser->id,
            ]);

            // Phát sóng cuộc hội thoại mới tới các quản trị viên
            event(new NewConversationCreated($conversation));
        }

        // Trả về user_id và conversation_id cùng với tin nhắn chào mừng
        $response = response()->json([
            'message' => $isNewGuest ? 'Guest registered successfully.' : 'Guest recognized, conversation loaded.',
            'user_id' => $guestUser->id,
            'conversation_id' => $conversation->id,
            'conversation_messages' => $conversation->messages->map(function($msg) use ($guestUser) { // Dùng map để định dạng tin nhắn
                return [
                    'content' => $msg->content,
                    'sender_id' => $msg->sender_id,
                    'created_at' => $msg->created_at,
                    'is_sent_by_current_user' => ($msg->sender_id == $guestUser->id), // Để frontend biết tin nhắn của ai
                ];
            })->toArray(),
            'is_new_guest' => $isNewGuest,
        ]);

        // Đặt cookie để duy trì guest_user_id
        return $response->cookie('guest_user_id', $guestUser->id, 60*24*30); // Cookie 30 ngày
    }

    // Gửi tin nhắn
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:chat_conversations,id',
            'content' => 'required|string',
            'type' => 'in:text,image,file,system',
            'sender_id' => 'required|exists:users,id', // sender_id phải được cung cấp từ frontend
        ]);

        $sender = User::find($request->sender_id);

        if (!$sender || (!$sender->is_guest && $sender->id !== Auth::id())) {
            return response()->json(['message' => 'Invalid sender or unauthorized.'], 403);
        }

        $conversation = null;

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
            event(new NewConversationCreated($conversation));
        } else {
            $conversation = ChatConversation::find($request->conversation_id);
            if (!$conversation) {
                return response()->json(['message' => 'Conversation not found.'], 404);
            }
            $conversation->update(['last_message_at' => now()]);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
        ]);

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

            ChatConversation::where('user_id', $guestUser->id)->update(['user_id' => $realUser->id]);
            ChatMessage::where('sender_id', $guestUser->id)->update(['sender_id' => $realUser->id]);
            ChatParticipant::where('user_id', $guestUser->id)->update(['user_id' => $realUser->id]);

            $guestUser->delete();

            return response()->json(['message' => 'Login successful, accounts merged.'])
                             ->withoutCookie('guest_user_id');
        }

        return response()->json(['message' => 'Invalid credentials.'], 401);
    }
}
