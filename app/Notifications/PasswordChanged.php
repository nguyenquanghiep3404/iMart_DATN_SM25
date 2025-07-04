<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class PasswordChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast']; // Gửi email + in-app + realtime nếu có
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Xác nhận đổi mật khẩu')
            ->line('Mật khẩu của bạn đã được thay đổi thành công.')
            ->line('Nếu bạn không thực hiện việc này, vui lòng liên hệ chúng tôi ngay lập tức.')
            ->action('Liên hệ hỗ trợ', url('/help'))
            ->line('Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Mật khẩu của bạn đã được thay đổi thành công.',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'Mật khẩu của bạn đã được thay đổi thành công.',
        ]);
    }
}
