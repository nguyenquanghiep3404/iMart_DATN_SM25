<?php

namespace App\Events;

use App\Models\Order; // <-- QUAN TRỌNG: Import model Order
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderDelivered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Biến public để lưu trữ thông tin đơn hàng.
     * Listener sẽ truy cập vào biến này.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * Hàm khởi tạo event.
     * Khi gọi event(new OrderDelivered($order)),
     * biến $order sẽ được truyền vào đây.
     *
     * @param \App\Models\Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order; // Gán đơn hàng được truyền vào cho biến public
    }
}
