<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with('student');

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('installment_number')->get();

        return response()->json($payments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'installment_number' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid',
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // If status is paid and no paid_at provided, set it to now
        if ($validated['status'] === 'paid' && !isset($validated['paid_at'])) {
            $validated['paid_at'] = now();
        }

        $payment = Payment::create($validated);

        return response()->json($payment, 201);
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,paid',
            'paid_at' => 'nullable|date',
            'amount' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // If status is changed to paid and no paid_at provided, set it to now
        if (isset($validated['status']) && $validated['status'] === 'paid' && !isset($validated['paid_at']) && !$payment->paid_at) {
            $validated['paid_at'] = now();
        }

        $payment->update($validated);

        return response()->json($payment);
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(null, 204);
    }

    public function markAsPaid(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $payment->update([
            'status' => 'paid',
            'paid_at' => $validated['paid_at'] ?? now(),
            'notes' => $validated['notes'] ?? $payment->notes,
        ]);

        return response()->json($payment);
    }
}
