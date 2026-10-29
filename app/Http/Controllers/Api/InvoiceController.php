<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvoicePaidNotification;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        $invoices = Invoice::with('student.package.installments')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'invoice_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'description' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'vat_rate' => 'numeric|min:0|max:100',
            'installment_number' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'mark_as_paid' => 'boolean',
            'custom_invoice_number' => 'nullable|string|max:50',
            'use_custom_number' => 'boolean',
        ]);

        $student = Student::with('package.installments')->findOrFail($validated['student_id']);
        if (isset($validated['installment_number'])) {
            if ($student->payment_method !== 'installments'
                || ! in_array((int) $validated['installment_number'], $student->installmentNumbers(), true)) {
                throw ValidationException::withMessages(['installment_number' => 'Odaberite ratu iz plana plaćanja ovog upisa.']);
            }
            if ($student->invoices()->where('installment_number', $validated['installment_number'])
                ->where('status', '!=', 'cancelled')->exists()) {
                throw ValidationException::withMessages(['installment_number' => 'Za ovu ratu i ovaj upis već postoji faktura. Otvorite postojeću fakturu.']);
            }
        }

        $vatRate = $validated['vat_rate'] ?? 20;
        $amounts = Invoice::calculateFromGross($validated['total_amount'], $vatRate);

        // Determine invoice number
        $useCustomNumber = $validated['use_custom_number'] ?? false;
        $invoiceNumber = $useCustomNumber && ! empty($validated['custom_invoice_number'])
            ? $validated['custom_invoice_number']
            : Invoice::generateInvoiceNumber();

        $invoice = DB::transaction(function () use ($validated, $invoiceNumber, $useCustomNumber, $vatRate, $amounts) {
            $invoice = Invoice::create([
                'student_id' => $validated['student_id'],
                'invoice_number' => $invoiceNumber,
                'is_custom_number' => $useCustomNumber,
                'invoice_date' => $validated['invoice_date'],
                'payment_date' => $validated['payment_date'] ?? (($validated['mark_as_paid'] ?? false) ? now() : null),
                'description' => $validated['description'],
                'net_amount' => $amounts['net_amount'],
                'vat_rate' => $vatRate,
                'vat_amount' => $amounts['vat_amount'],
                'total_amount' => $amounts['total_amount'],
                'installment_number' => $validated['installment_number'] ?? null,
                'status' => ($validated['mark_as_paid'] ?? false) ? 'paid' : 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create payment record if marking as paid
            if ($validated['mark_as_paid'] ?? false) {
                Payment::create([
                    'student_id' => $validated['student_id'],
                    'invoice_id' => $invoice->id,
                    'installment_number' => $validated['installment_number'] ?? 1,
                    'amount' => $amounts['total_amount'],
                    'paid_at' => $validated['payment_date'] ?? now(),
                    'status' => 'paid',
                ]);
            }

            return $invoice;
        });

        return response()->json($invoice->load('student.package.installments'), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load('student.package.installments'));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,paid,cancelled',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            if (($validated['status'] ?? $invoice->status) === 'paid' && ! $invoice->payment_date && empty($validated['payment_date'])) {
                $validated['payment_date'] = now();
            }
            $invoice->update($validated);
            if ($invoice->status === 'paid') {
                Payment::updateOrCreate(
                    [
                        'student_id' => $invoice->student_id,
                        'invoice_id' => $invoice->id,
                    ],
                    [
                        'installment_number' => $invoice->installment_number ?? 1,
                        'amount' => $invoice->total_amount,
                        'paid_at' => $invoice->payment_date ?? now(),
                        'status' => 'paid',
                    ]
                );
            } else {
                Payment::where('invoice_id', $invoice->id)->update(['status' => 'pending', 'paid_at' => null]);
            }
        });

        return response()->json($invoice->load('student.package.installments'));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json(null, 204);
    }

    public function downloadPdf(Invoice $invoice): Response
    {
        $invoice->load('student.package');
        $student = $invoice->student;

        if (! $student) {
            abort(404, 'Student not found');
        }

        $logoPath = public_path('logo.jpg');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/jpeg;base64,'.base64_encode($logoData);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'student' => $student,
            'logoPath' => $logoBase64,
        ]);

        $filename = sprintf('Rechnung_%s.pdf', str_replace('/', '_', $invoice->invoice_number));

        return $pdf->download($filename);
    }

    public function sendInvoice(Invoice $invoice): JsonResponse
    {
        $invoice->load('student.package');
        $student = $invoice->student;

        if (! $student) {
            return response()->json(['message' => 'Student nije pronadjen.'], 404);
        }

        if (! $student->email) {
            return response()->json(['message' => 'Student nema email adresu.'], 422);
        }

        Mail::to($student->email)->send(new InvoicePaidNotification($invoice));

        return response()->json([
            'message' => 'Faktura je uspjesno poslana klijentu.',
            'email' => $student->email,
        ]);
    }

    public function sendInvoiceTest(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice->load('student.package');
        $adminEmail = $request->user()?->email;

        if (! $adminEmail) {
            return response()->json(['message' => 'Admin email nije pronadjen.'], 422);
        }

        Mail::to($adminEmail)->send(new InvoicePaidNotification($invoice));

        return response()->json([
            'message' => 'Test faktura je uspjesno poslana na admin email.',
            'email' => $adminEmail,
        ]);
    }

    public function getStudentPaymentStatus(): JsonResponse
    {
        $students = Student::with(['payments', 'invoices', 'package.installments'])
            ->where('payment_method', 'installments')->get()->map(function ($student) {
                return array_merge($student->paymentSummary(), [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'email' => $student->email,
                    'package_type' => $student->package_type,
                    'course_name' => $student->package?->name ?? $student->package_type,
                    'paid_installments' => $student->paid_installments_count,
                    'payments' => $student->payments,
                    'invoices' => $student->invoices,
                ]);
            });

        return response()->json($students);
    }

    public function getStudentsWithUnpaidInstallments(): JsonResponse
    {
        $students = Student::with(['payments', 'invoices', 'package.installments'])
            ->where('payment_method', 'installments')->get()->map(function ($student) {
                $paid = $student->paidInstallmentNumbers();
                $unpaid = array_values(array_diff($student->installmentNumbers(), $paid));

                return array_merge($student->paymentSummary(), [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'email' => $student->email,
                    'package_type' => $student->package_type,
                    'course_name' => $student->package?->name ?? $student->package_type,
                    'payment_method' => $student->payment_method,
                    'address' => $student->address,
                    'city' => $student->city,
                    'paid_installments' => $paid,
                    'unpaid_installments' => $unpaid,
                    'unpaid_count' => count($unpaid),
                ]);
            })->filter(fn ($student) => $student['unpaid_count'] > 0)->values();

        return response()->json($students);
    }
}
