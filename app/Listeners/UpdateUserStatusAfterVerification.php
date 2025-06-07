<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UpdateUserStatusAfterVerification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Verified $event)
    {
       $user = User::find($event->user->id); // hoặc $event->user->getKey()

        if ($user) { // Đảm bảo người dùng thực sự tồn tại trong CSDL

            // Kiểm tra xem user có đang ở trạng thái 'inactive' không để tránh cập nhật không cần thiết
            if ($user->status === 'inactive') {
                $user->status = 'active';
                $user->save(); // BÂY GIỜ PHƯƠNG THỨC SAVE() SẼ HOẠT ĐỘNG

                // (Tùy chọn) Ghi log để theo dõi và gỡ lỗi nếu cần
                Log::info("ID người dùng {$user->id} ({$user->email}) đã được kích hoạt sau khi xác minh email.");
            }
        }
    }
}
