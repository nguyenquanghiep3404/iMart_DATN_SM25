<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelUnpaidQrOrders extends Command
{
    /**
     * Tên và chữ ký của command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid-qr';

    /**
     * Mô tả của command.
     *
     * @var string
     */
    protected $description = 'Tự động hủy các đơn hàng thanh toán QR chưa được thanh toán sau 1 ngày';

    /**
     * Thực thi command.
     */
    public function handle()
    {
        $this->info('Bắt đầu quét các đơn hàng QR quá hạn...');
        Log::info('Cronjob: Bắt đầu quét các đơn hàng QR quá hạn.');

        // Lấy mốc thời gian là 24 giờ trước
        $expirationTime = Carbon::now()->subDay();

        // Tìm các đơn hàng thỏa mãn điều kiện
        $expiredOrders = Order::where('payment_method', 'bank_transfer_qr')
            ->where('payment_status', Order::PAYMENT_PENDING) // Hoặc 'pending'
            ->where('status', Order::STATUS_PENDING_CONFIRMATION) // Hoặc 'pending_confirmation'
            ->where('created_at', '<=', $expirationTime)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('Không tìm thấy đơn hàng nào quá hạn.');
            Log::info('Cronjob: Không tìm thấy đơn hàng QR nào quá hạn.');
            return;
        }

        $this->info("Tìm thấy {$expiredOrders->count()} đơn hàng cần hủy.");
        Log::info("Cronjob: Tìm thấy {$expiredOrders->count()} đơn hàng QR cần hủy.");

        foreach ($expiredOrders as $order) {
            $order->status = Order::STATUS_CANCELLED; // Hoặc 'cancelled'
            $order->payment_status = Order::PAYMENT_FAILED; // Cập nhật trạng thái thanh toán thành 'failed'
            $order->cancellation_reason = 'Tự động hủy do quá hạn thanh toán QR.';
            $order->cancelled_at = Carbon::now();
            $order->save();

            // QUAN TRỌNG: Hoàn trả tồn kho (nếu cần)
            // Dựa vào code PaymentController của bạn, tồn kho chưa bị trừ khi tạo đơn hàng QR,
            // nên có thể bạn không cần bước này. Nhưng nếu logic của bạn có trừ tồn kho,
            // bạn phải thêm code để cộng lại tồn kho ở đây.
            // Ví dụ:
            // foreach ($order->items as $item) {
            //     $variant = $item->productVariant;
            //     if ($variant && $variant->manage_stock) {
            //         // Viết hàm để cộng lại tồn kho
            //         // $this->incrementInventoryStock($variant, $item->quantity);
            //     }
            // }

            $this->info("Đã hủy đơn hàng: {$order->order_code}");
            Log::info("Cronjob: Đã hủy đơn hàng QR {$order->order_code}");
        }

        $this->info('Hoàn thành việc quét và hủy đơn hàng.');
        Log::info('Cronjob: Hoàn thành việc quét và hủy đơn hàng QR.');
    }
}