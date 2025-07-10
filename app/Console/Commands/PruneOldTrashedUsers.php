<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PruneOldTrashedUsers extends Command
{
    /**
     * Tên và chữ ký của command.
     * @var string
     */
    protected $signature = 'users:prune-trashed';

    /**
     * Mô tả của command.
     * @var string
     */
    protected $description = 'Tự động xóa vĩnh viễn các người dùng đã nằm trong thùng rác hơn 90 ngày';

    /**
     * Thực thi logic của command.
     */
    public function handle()
    {
        $this->info('Bắt đầu quá trình dọn dẹp người dùng cũ trong thùng rác...');

        // Tính toán ngày giới hạn (90 ngày trước)
        $cutoffDate = now()->subDays(90);

        // Lấy danh sách các user cần xóa
        $usersToDelete = User::onlyTrashed()
                            ->where('deleted_at', '<=', $cutoffDate)
                            ->get();

        if ($usersToDelete->isEmpty()) {
            $this->info('Không có người dùng nào cần xóa.');
            return Command::SUCCESS;
        }

        $this->info("Tìm thấy {$usersToDelete->count()} người dùng để xóa vĩnh viễn.");

        foreach ($usersToDelete as $user) {
            // Quan trọng: Xóa file avatar liên quan trước khi xóa vĩnh viễn user
            // để kích hoạt event 'deleting' trong UploadedFile model
            if ($user->avatar) {
                $user->avatar->delete();
            }

            // Thực hiện xóa vĩnh viễn user khỏi database
            $user->forceDelete();

            $logMessage = "Đã xóa vĩnh viễn người dùng ID: {$user->id}, Email: {$user->email}";
            Log::info($logMessage);
            $this->line($logMessage);
        }

        $this->info('Hoàn tất quá trình dọn dẹp.');
        return Command::SUCCESS;
    }
}
