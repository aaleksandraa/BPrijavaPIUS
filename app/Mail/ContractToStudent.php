<?php

namespace App\Mail;

use App\Models\Contract;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractToStudent extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public Contract $contract
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vaš ugovor - PIUS ACADEMY',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-to-student',
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.contract', [
            'contract' => $this->contract,
            'student' => $this->student,
        ]);

        $filename = sprintf(
            'Ugovor_%s_%s_%s.pdf',
            $this->student->first_name,
            $this->student->last_name,
            now()->format('Y-m-d')
        );

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
