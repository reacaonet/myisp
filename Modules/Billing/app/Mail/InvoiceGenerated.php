<?php

namespace Modules\Billing\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Invoice;

class InvoiceGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Fatura {$this->invoice->invoice_number} - Vencimento {$this->invoice->due_date->format('d/m/Y')}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'billing::emails.invoice-generated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
