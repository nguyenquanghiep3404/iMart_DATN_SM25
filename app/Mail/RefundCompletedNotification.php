<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefundCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $returnRequest;

    public function __construct(ReturnRequest $returnRequest)
    {
        $this->returnRequest = $returnRequest;
    }

    public function build()
    {
        return $this->subject('Hoàn tiền đơn hàng #' . $this->returnRequest->return_code)
                    ->view('emails.refunds_completed');
    }
}
