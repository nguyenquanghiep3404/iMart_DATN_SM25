<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;


class EmailChanged extends Notification
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $this->user->getKey(), 'hash' => sha1($this->user->getEmailForVerification())]
        );

        return (new MailMessage)
            ->subject('Địa chỉ email của bạn đã được thay đổi')
            ->line('Địa chỉ email của bạn đã được cập nhật.')
            ->line('Vui lòng xác thực địa chỉ email mới của bạn bằng cách nhấn nút bên dưới.')
            ->action('Xác thực Email', $verificationUrl)
            ->line('Nếu bạn không thực hiện việc này, vui lòng liên hệ bộ phận hỗ trợ.')
            ->line('Truy cập Trung tâm trợ giúp tại: ' . url('/help'));
    }
}
