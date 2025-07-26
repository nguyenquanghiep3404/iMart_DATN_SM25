<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbandonedCartReminder extends Notification
{
    use Queueable;

    protected array $channels;

    /**
     * Khởi tạo notification với danh sách kênh (mail/database)
     */
    public function __construct(array $channels = ['mail', 'database'])
    {
        $this->channels = $channels;
    }

    /**
     * Xác định kênh gửi thông báo
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Thông báo in-app (ghi vào database)
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Bạn còn sản phẩm trong giỏ hàng!',
            'message' => 'Hãy hoàn tất đơn hàng của bạn trước khi sản phẩm hết hàng.',
            'action_url' => url('/cart'),
        ];
    }

    /**
     * Gửi email
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🛒 Bạn còn sản phẩm trong giỏ hàng!')
            ->greeting('Xin chào ' . ($notifiable->name ?? 'bạn') . ' 👋')
            ->line('Chúng tôi nhận thấy bạn đã thêm sản phẩm vào giỏ nhưng chưa hoàn tất đơn hàng.')
            ->line('Hãy quay lại hoàn tất đơn hàng trước khi sản phẩm hết hàng!')
            ->action('Xem giỏ hàng của bạn', url('/cart'))
            ->line('Cảm ơn bạn đã mua sắm tại ' . config('app.name') . '!');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
