<?php

namespace App\Http\Controllers\Users;

use App\Events\NewConversationCreated;
use App\Events\NewMessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Quan trọng: Thêm import cho Transaction
use Illuminate\Support\Facades\Log; // Quan trọng: Thêm import để ghi log lỗi
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Hiển thị giao diện chat chính.
     * Logic này sẽ được hoàn thiện để tải các cuộc hội thoại của người dùng/khách.
     */
    public function index(Request $request)
    {
        // Giữ nguyên logic debug để đảm bảo view hoạt động
        $conversations = collect();
        $greeting = "Chào bạn!";
        $guestUserId = null;

        return view('users.partials.ai_chatbot', compact('conversations', 'greeting', 'guestUserId'));
    }

    /**
     * Đăng ký người dùng khách khi họ bắt đầu chat.
     */
    public function registerGuest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
        ]);

        $guestUser = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => 'guest_'.Str::uuid().'@example.com', // Email giả
            'password' => bcrypt(Str::random(16)), // Mật khẩu giả
            'is_guest' => true,
            'status' => 'active',
        ]);

        // Tạo cuộc hội thoại ban đầu
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

        // Phát sóng sự kiện có cuộc hội thoại mới
        event(new NewConversationCreated($conversation));

        $response = response()->json([
            'message' => 'Guest registered successfully.',
            'user_id' => $guestUser->id,
            'conversation_id' => $conversation->id,
        ]);

        // Đặt cookie để duy trì trạng thái khách
        return $response->cookie('guest_user_id', $guestUser->id, 60 * 24 * 30); // 30 ngày
    }

    /**
     * Gửi tin nhắn mới.
     * *** ĐÃ CẬP NHẬT LOGIC BẢO MẬT ***
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:chat_conversations,id',
            'content' => 'required|string',
            'type' => 'in:text,image,file,system',
            'sender_id' => 'required|exists:users,id',
        ]);

        $sender = null;

        // --- Bắt đầu logic bảo mật đã được siết chặt ---
        if (Auth::check()) {
            // Nếu đã đăng nhập, người gửi BẮT BUỘC phải là người dùng hiện tại.
            $user = Auth::user();
            if ((int) $request->sender_id !== $user->id) {
                return response()->json(['message' => 'Sender ID mismatch.'], 403);
            }
            $sender = $user;
        } else {
            // Nếu là khách, xác thực sender_id từ request với cookie.
            $guestUserIdFromCookie = $request->cookie('guest_user_id');
            if (! $guestUserIdFromCookie || (int) $request->sender_id !== (int) $guestUserIdFromCookie) {
                return response()->json(['message' => 'Guest session mismatch or invalid sender.'], 403);
            }

            $sender = User::find($request->sender_id);
            // Kiểm tra thêm để chắc chắn ID này là của một tài khoản khách hợp lệ
            if (! $sender || ! $sender->is_guest) {
                return response()->json(['message' => 'Invalid guest user.'], 403);
            }
        }
        // --- Kết thúc logic bảo mật ---

        // Từ đây, biến $sender đã được xác thực và đáng tin cậy.

        $conversation = null;
        if (! $request->conversation_id) {
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
            if (! $conversation) {
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

        // Phát sóng tin nhắn mới
        broadcast(new NewMessageSent($message, $conversation))->toOthers();

        return response()->json(['message' => 'Message sent!', 'data' => ['conversation_id' => $conversation->id]]);
    }

    /**
     * Hợp nhất tài khoản khách vào tài khoản thật khi đăng nhập.
     * *** ĐÃ CẬP NHẬT ĐỂ SỬ DỤNG DATABASE TRANSACTION ***
     */
    public function guestLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'guest_user_id' => 'required|exists:users,id,is_guest,1',
        ]);

        $guestUser = User::find($request->guest_user_id);
        if (! $guestUser) {
            return response()->json(['message' => 'Guest user not found.'], 404);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $realUser = Auth::user();

            // Bắt đầu Transaction
            DB::beginTransaction();
            try {
                // Hợp nhất dữ liệu: Cập nhật user_id trong các bảng liên quan
                ChatConversation::where('user_id', $guestUser->id)->update(['user_id' => $realUser->id]);
                ChatMessage::where('sender_id', $guestUser->id)->update(['sender_id' => $realUser->id]);
                ChatParticipant::where('user_id', $guestUser->id)->update(['user_id' => $realUser->id]);

                // Xóa tài khoản khách
                $guestUser->delete();

                // Nếu tất cả các thao tác thành công, commit transaction
                DB::commit();

            } catch (\Exception $e) {
                // Nếu có bất kỳ lỗi nào, rollback tất cả các thay đổi
                DB::rollBack();

                // Ghi log lỗi để debug
                Log::error('Failed to merge guest account: '.$e->getMessage());

                return response()->json(['message' => 'An error occurred during account merge.'], 500);
            }

            // Xóa cookie guest_user_id sau khi hợp nhất thành công
            return response()->json(['message' => 'Login successful, accounts merged.'])
                ->withoutCookie('guest_user_id');
        }

        return response()->json(['message' => 'Invalid credentials.'], 401);
    }
}
