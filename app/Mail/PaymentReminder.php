<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailContent;

    public function __construct(
        public Invoice $invoice
    ) {
        $this->invoice->load('student');
        $this->emailContent = $this->parseTemplate();
    }

    protected function parseTemplate(): string
    {
        $template = Setting::get('payment_reminder_email_template', $this->getDefaultTemplate());

        $replacements = [
            '{name}' => $this->invoice->student->first_name . ' ' . $this->invoice->student->last_name,
            '{first_name}' => $this->invoice->student->first_name,
            '{last_name}' => $this->invoice->student->last_name,
            '{invoice_number}' => $this->invoice->invoice_number,
            '{description}' => $this->invoice->description,
            '{amount}' => '€' . number_format($this->invoice->total_amount, 2, ',', '.'),
            '{due_date}' => $this->invoice->invoice_date->format('d.m.Y'),
            '{email}' => $this->invoice->student->email,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function getDefaultTemplate(): string
    {
        return "Sehr geehrte/r {name},

wir möchten Sie freundlich daran erinnern, dass die folgende Zahlung in Kürze fällig ist:

Rechnungsnummer: {invoice_number}
Beschreibung: {description}
Betrag: {amount}
Fälligkeitsdatum: {due_date}

Bitte überweisen Sie den Betrag rechtzeitig.

Mit freundlichen Grüßen,
Studio PIUS Team";
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@studiopius.at', 'Studio PIUS'),
            replyTo: [new Address('noreply@studiopius.at', 'Studio PIUS (No Reply)')],
            subject: 'Zahlungserinnerung - ' . $this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
            with: ['emailContent' => $this->emailContent],
        );
    }
}
