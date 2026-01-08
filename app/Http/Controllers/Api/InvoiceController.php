<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        $invoices = Invoice::with('student')
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
            'installment_number' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string',
            'mark_as_paid' => 'boolean',
        ]);

        $vatRate = $validated['vat_rate'] ?? 20;
        $amounts = Invoice::calculateFromGross($validated['total_amount'], $vatRate);

        $invoice = Invoice::create([
            'student_id' => $validated['student_id'],
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => $validated['invoice_date'],
            'payment_date' => $validated['payment_date'] ?? null,
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

        return response()->json($invoice->load('student'), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load('student'));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,paid,cancelled',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $wasNotPaid = $invoice->status !== 'paid';
        $invoice->update($validated);

        // Create payment record if status changed to paid
        if ($wasNotPaid && ($validated['status'] ?? null) === 'paid') {
            Payment::updateOrCreate(
                [
                    'student_id' => $invoice->student_id,
                    'invoice_id' => $invoice->id,
                ],
                [
                    'installment_number' => $invoice->installment_number ?? 1,
                    'amount' => $invoice->total_amount,
                    'paid_at' => $validated['payment_date'] ?? now(),
                    'status' => 'paid',
                ]
            );
        }

        return response()->json($invoice->load('student'));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();
        return response()->json(null, 204);
    }

    public function downloadPdf(Invoice $invoice): Response
    {
        $invoice->load('student');
        $student = $invoice->student;

        if (!$student) {
            abort(404, 'Student not found');
        }

        $logoPath = public_path('logo.jpg');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'student' => $student,
            'logoPath' => $logoBase64,
        ]);

        $filename = sprintf('Rechnung_%s.pdf', str_replace('/', '_', $invoice->invoice_number));

        return $pdf->download($filename);
    }

    public function getStudentPaymentStatus(): JsonResponse
    {
        $students = Student::with(['payments', 'invoices'])
            ->where('payment_method', 'installments')
            ->get()
            ->map(function ($student) {
                $paidCount = $student->payments->where('status', 'paid')->count();
                $totalInstallments = 3;

                return [
                    'id' => $student->id,
                    'name' => $student->full_name,
                    'email' => $student->email,
                    'package_type' => $student->package_type,
                    'paid_installments' => $paidCount,
                    'total_installments' => $totalInstallments,
                    'payment_status' => "{$paidCount}/{$totalInstallments}",
                    'payments' => $student->payments,
                    'invoices' => $student->invoices,
                ];
            });

        return response()->json($students);
    }

    public function getStudentsWithUnpaidInstallments(): JsonResponse
    {
        $students = Student::with(['invoices' => function ($query) {
            $query->where('status', 'paid');
        }])
            ->where('payment_method', 'installments')
            ->get()
            ->map(function ($student) {
                // Get paid installment numbers
                $paidInstallments = $student->invoices
                    ->whereNotNull('installment_number')
                    ->pluck('installment_number')
                    ->toArray();

                $totalInstallments = 3;
                $unpaidCount = $totalInstallments - count($paidInstallments);

                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'email' => $student->email,
                    'package_type' => $student->package_type,
                    'payment_method' => $student->payment_method,
                    'address' => $student->address,
                    'city' => $student->city,
                    'paid_installments' => $paidInstallments,
                    'unpaid_count' => $unpaidCount,
                ];
            })
            ->filter(function ($student) {
                return $student['unpaid_count'] > 0;
            })
            ->values();

        return response()->json($students);
    }
}
