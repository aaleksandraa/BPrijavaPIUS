<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentReminder;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $defaultEmailTemplate = "Sehr geehrte/r {name},

wir möchten Sie freundlich daran erinnern, dass die folgende Zahlung in Kürze fällig ist:

Rechnungsnummer: {invoice_number}
Beschreibung: {description}
Betrag: {amount}
Fälligkeitsdatum: {due_date}

Bitte überweisen Sie den Betrag rechtzeitig auf folgendes Konto:

Bankverbindung:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BIC: RLNWATWWGTD
Verwendungszweck: {invoice_number}

Falls Sie die Zahlung bereits getätigt haben, betrachten Sie diese E-Mail bitte als gegenstandslos.

Bei Fragen stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen,
Studio PIUS Team";

        $settings = [
            'payment_reminder_enabled' => Setting::getBool('payment_reminder_enabled'),
            'payment_reminder_days_before' => (int) Setting::get('payment_reminder_days_before', 2),
            'payment_reminder_email_template' => Setting::get('payment_reminder_email_template', $defaultEmailTemplate),
        ];

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_reminder_enabled' => 'sometimes|boolean',
            'payment_reminder_days_before' => 'sometimes|integer|min:1|max:30',
            'payment_reminder_email_template' => 'sometimes|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
        }

        return response()->json(['message' => 'Settings updated']);
    }

    public function sendPaymentReminder(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice->load('student');

        if (!$invoice->student || !$invoice->student->email) {
            return response()->json(['error' => 'Student email not found'], 400);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['error' => 'Invoice is already paid'], 400);
        }

        try {
            Mail::to($invoice->student->email)->send(new PaymentReminder($invoice));
            return response()->json(['message' => 'Reminder sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send reminder: ' . $e->getMessage()], 500);
        }
    }
}
