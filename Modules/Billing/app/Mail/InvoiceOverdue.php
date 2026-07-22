<?php

namespace Modules\Billing\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Invoice;

class InvoiceOverdue extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public int $daysOverdue;

    public function __construct(Invoice $invoice, int $daysOverdue = 0)
    {
        $this->invoice = $invoice;
        $this->daysOverdue = $daysOverdue;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Fatura atrasada - {$this->invoice->invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'billing::emails.invoice-overdue',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
