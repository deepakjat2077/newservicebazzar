<?php

namespace App\Mail;

use App\Models\SalesOrder;
use App\Support\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The payment receipt: a short note with the tax invoice attached as a PDF. */
class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SalesOrder $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . Invoice::numberFor($this->order) . ' — ' . config('invoice.company.trade_name', 'Service Bazzar'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: ['data' => Invoice::data($this->order)],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => Invoice::pdf($this->order), Invoice::filename($this->order))
                ->withMime('application/pdf'),
        ];
    }
}
