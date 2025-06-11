<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UploadedFile;
use App\Services\FileService;
use Illuminate\Support\Facades\Log;

class CleanupTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-temp-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes temporary uploaded files that were not associated with any model after a certain period (e.g., 24 hours).';

    /**
     * Execute the console command.
     */
    public function handle(FileService $fileService)
    {
        $this->info('Starting cleanup of temporary uploaded files...');
        Log::info('Scheduled Task: Starting cleanup of temporary files.');

        // Định nghĩa thời gian giới hạn, ví dụ: các file cũ hơn 24 giờ
        $cutoffTime = now()->subHours(24);

        // Tìm tất cả các file chưa được đính kèm vào đâu VÀ được tạo ra trước thời gian giới hạn
        $filesToDelete = UploadedFile::whereNull('attachable_id')
                                     ->where('created_at', '<', $cutoffTime)
                                     ->get();

        if ($filesToDelete->isEmpty()) {
            $this->info('No temporary files to clean up.');
            Log::info('Scheduled Task: No temporary files to clean up.');
            return 0;
        }

        $count = $filesToDelete->count();
        $this->info("Found {$count} temporary file(s) to delete.");
        Log::info("Scheduled Task: Found {$count} temporary file(s) to delete.");

        foreach ($filesToDelete as $file) {
            try {
                // Lệnh $file->delete() sẽ kích hoạt Model Event để xóa file vật lý
                $file->delete();
                $this->line("Deleted record and file ID: {$file->id}, Path: {$file->path}");
            } catch (\Exception $e) {
                $this->error("Failed to delete file ID: {$file->id}. Error: " . $e->getMessage());
                Log::error("Scheduled Task: Failed to delete file ID: {$file->id}. Error: " . $e->getMessage());
            }
        }

        $this->info('Cleanup completed successfully.');
        Log::info('Scheduled Task: Cleanup completed successfully.');
        return 0;
    }
}
