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
        Auth::login($guestUser);
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
     * *** ĐÃ CẬP NHẬT HOÀN CHỈNH LOGIC & BẢO MẬT ***
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

        // --- Bắt đầu logic xác thực người gửi (Giữ nguyên vì đã tốt) ---
        if (Auth::check()) {
            $user = Auth::user();
            if ((int) $request->sender_id !== $user->id) {
                return response()->json(['message' => 'Sender ID mismatch.'], 403);
            }
            $sender = $user;
        } else {
            $guestUserIdFromCookie = $request->cookie('guest_user_id');
            if (! $guestUserIdFromCookie || (int) $request->sender_id !== (int) $guestUserIdFromCookie) {
                return response()->json(['message' => 'Guest session mismatch or invalid sender.'], 403);
            }
            $sender = User::find($request->sender_id);
            if (! $sender || ! $sender->is_guest) {
                return response()->json(['message' => 'Invalid guest user.'], 403);
            }
        }
        // --- Kết thúc logic xác thực người gửi ---

        // Từ đây, biến $sender đã được xác thực và đáng tin cậy.

        $conversation = null;

        // ✅ [SỬA LỖI] Logic xử lý cuộc hội thoại
        if ($request->filled('conversation_id')) {
            // Trường hợp gửi tin nhắn vào cuộc hội thoại đã có
            $conversation = ChatConversation::find($request->conversation_id);

            // 🛑 [BẢO MẬT] Kiểm tra xem người gửi có quyền trong cuộc hội thoại này không
            $isParticipant = ChatParticipant::where('conversation_id', $conversation->id)
                                            ->where('user_id', $sender->id)
                                            ->exists();

            if (!$isParticipant) {
                return response()->json(['message' => 'You are not authorized to access this conversation.'], 403);
            }

            $conversation->update(['last_message_at' => now()]);

        } else {
            // ✅ [SỬA LỖI RACE CONDITION] Trường hợp tin nhắn đầu tiên, tìm hoặc tạo mới.
            $conversation = ChatConversation::firstOrCreate(
                [
                    // Điều kiện để xác định cuộc hội thoại là duy nhất
                    'user_id' => $sender->id,
                    'status' => 'open',
                    'type' => 'support',
                ],
                [
                    // Dữ liệu sẽ được thêm vào nếu tạo mới
                    'last_message_at' => now(),
                ]
            );

            // Nếu cuộc hội thoại vừa được tạo (wasRecentlyCreated)
            if ($conversation->wasRecentlyCreated) {
                ChatParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $sender->id,
                ]);
                // Thông báo cho admin/support có cuộc hội thoại mới
                event(new NewConversationCreated($conversation));
            }
        }
        
        // Tạo tin nhắn
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
        ]);

        // Nạp thông tin người gửi để hiển thị ở client mà không cần truy vấn lại
        $message->load('sender');

        // Phát sóng tin nhắn mới đến các client khác trong kênh
        broadcast(new NewMessageSent($message, $conversation))->toOthers();

        return response()->json([
            'message' => 'Message sent successfully!',
            'data' => [
                'message' => $message, // Trả về cả dữ liệu tin nhắn vừa tạo
            ]
        ]);
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
    public function getHistory(Request $request)
{
    $userId = null;
    $user = Auth::user(); // Luôn ưu tiên người dùng đã đăng nhập qua session

    // TRƯỜNG HỢP 1: Người dùng đã đăng nhập thực sự (không phải khách)
    if ($user && !$user->is_guest) {
        $userId = $user->id;
    } 
    // TRƯỜNG HỢP 2: Là khách vãng lai
    else {
        // Xác thực user_id gửi lên phải là của một tài khoản khách
        $request->validate([
            'user_id' => 'required|integer|exists:users,id,is_guest,1',
        ]);
        $guestId = $request->input('user_id');

        // Để bảo mật, kiểm tra lại cookie khớp với user_id
        if ($guestId != $request->cookie('guest_user_id')) {
            return response()->json(['conversation' => null]);
        }
        $userId = $guestId;
    }

    if (!$userId) {
        return response()->json(['conversation' => null]);
    }

    // Tìm cuộc hội thoại đang mở gần nhất của user (dùng chung cho cả 2 trường hợp)
    $conversation = ChatConversation::where('user_id', $userId)
        ->where('status', 'open')
        ->orderBy('last_message_at', 'desc')
        ->with('messages') // Tải sẵn các tin nhắn
        ->first();

    // Chỉ thực hiện đăng nhập tạm thời nếu người dùng là khách
    if ($conversation && (!$user || $user->is_guest)) {
        Auth::loginUsingId($userId);
    }

    return response()->json([
        'conversation' => $conversation
    ]);
}


}