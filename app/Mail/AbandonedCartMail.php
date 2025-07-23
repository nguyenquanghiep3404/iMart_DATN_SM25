<?php

namespace App\Mail;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cart;

    /**
     * Create a new message instance.
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🛒 Bạn còn sản phẩm trong giỏ hàng!'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.abandoned_cart',
            with: [
                'cart' => $this->cart,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
