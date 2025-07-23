<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbandonedCartReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Bạn còn sản phẩm trong giỏ hàng!',
            'message' => 'Hãy hoàn tất đơn hàng của bạn trước khi sản phẩm hết hàng.',
            'action_url' => url('/cart'),
        ];
    }
    /**
     * Get the mail representation of the notification.
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

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
