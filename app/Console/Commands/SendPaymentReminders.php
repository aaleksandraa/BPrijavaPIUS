<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminder;
use App\Models\Invoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    protected $signature = 'reminders:send-payment';
    protected $description = 'Send payment reminders for invoices due in X days';

    public function handle(): int
    {
        if (!Setting::getBool('payment_reminder_enabled')) {
            $this->info('Payment reminders are disabled.');
            return 0;
        }

        $daysBefore = (int) Setting::get('payment_reminder_days_before', 2);
        $targetDate = Carbon::now()->addDays($daysBefore)->toDateString();

        $invoices = Invoice::with('student')
            ->where('status', 'pending')
            ->whereDate('invoice_date', $targetDate)
            ->get();

        $this->info("Found {$invoices->count()} invoices due on {$targetDate}");

        foreach ($invoices as $invoice) {
            if (!$invoice->student || !$invoice->student->email) {
                $this->warn("Skipping invoice {$invoice->invoice_number} - no student email");
                continue;
            }

            try {
                Mail::to($invoice->student->email)->send(new PaymentReminder($invoice));
                $this->info("Sent reminder for invoice {$invoice->invoice_number} to {$invoice->student->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for {$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        return 0;
    }
}
