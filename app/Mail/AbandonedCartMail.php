<?php

namespace App\Mail;

use App\Models\AbandonedCart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public $abandonedCart;

    public function __construct(AbandonedCart $abandonedCart)
    {
        $this->abandonedCart = $abandonedCart;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🛒 Bạn còn sản phẩm trong giỏ hàng!'
        );
    }

    public function content(): Content
    {
        $recoveryUrl = route('cart.restore', ['token' => $this->abandonedCart->recovery_token]);
        \Log::info('Recovery URL sent in email: ' . $recoveryUrl);

        return new Content(
            view: 'emails.abandoned_cart',
            with: [
                'user' => $this->abandonedCart->user,
                'cart' => $this->abandonedCart->cart,
                'recoveryUrl' => $recoveryUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
