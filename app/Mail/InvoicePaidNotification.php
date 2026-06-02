<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;

class InvoicePaidNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {
        $this->invoice->load('student');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'PIUS ACADEMY'),
            subject: 'Faktura ' . $this->invoice->invoice_number . ' - PIUS ACADEMY',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-paid',
            with: [
                'invoice' => $this->invoice,
                'student' => $this->invoice->student,
            ],
        );
    }

    public function attachments(): array
    {
        $filename = sprintf('Rechnung_%s.pdf', str_replace('/', '_', $this->invoice->invoice_number));

        return [
            Attachment::fromData(fn () => $this->pdfContent(), $filename)
                ->withMime('application/pdf'),
        ];
    }

    private function pdfContent(): string
    {
        $logoPath = public_path('logo.jpg');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
        }

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'student' => $this->invoice->student,
            'logoPath' => $logoBase64,
        ])->output();
    }
}
