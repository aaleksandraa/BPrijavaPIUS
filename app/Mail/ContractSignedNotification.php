<?php

namespace App\Mail;

use App\Models\Contract;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public Contract $contract
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novi ugovor potpisan - ' . $this->student->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-signed',
        );
    }
}
