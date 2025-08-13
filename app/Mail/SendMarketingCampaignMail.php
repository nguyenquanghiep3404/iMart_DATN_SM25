<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SendMarketingCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $contentHtml;
    public $campaignName;
    public $voucherCode;

    public function __construct($subjectLine, $contentHtml, $campaignName, $voucherCode = null)
    {
        $this->subjectLine = $subjectLine;
        $this->contentHtml = $contentHtml;
        $this->campaignName = $campaignName;
        $this->voucherCode = $voucherCode;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing_campaign',
            with: [
                'contentHtml' => $this->contentHtml,
                'campaignName' => $this->campaignName,
                'voucherCode' => $this->voucherCode,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
