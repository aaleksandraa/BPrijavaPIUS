<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Student::with(['contracts', 'payments', 'invoices', 'package.installments'])->orderBy('enrolled_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        $students = $query->get()->map(function ($student) {
            $student->paid_installments_count = $student->payments->where('status', 'paid')->count();
            $student->total_installments = $student->package && $student->package->installments
                ? $student->package->installments->count()
                : ($student->payment_method === 'installments' ? 3 : 1);
            $student->payment_status = $student->paid_installments_count . '/' . $student->total_installments;
            return $student;
        });

        return response()->json($students);
    }

    public function store(Request $request): JsonResponse
    {
        $validPackageSlugs = Package::pluck('slug')->toArray();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|unique:students,email',
            'id_document_number' => 'required|string|max:50',
            'entity_type' => 'required|in:individual,company',
            'payment_method' => 'required|in:full,installments',
            'package_type' => ['required', Rule::in($validPackageSlugs)],
            'company_name' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'company_postal_code' => 'nullable|string|max:20',
            'company_city' => 'nullable|string|max:255',
            'company_country' => 'nullable|string|max:255',
            'company_registration' => 'nullable|string|max:50',
        ]);

        $validated['status'] = $request->input('status', 'enrolled');
        $validated['enrolled_at'] = $request->input('enrolled_at', now());

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json($student->load(['contracts', 'package.installments']));
    }

    public function update(Request $request, Student $student): JsonResponse
    {
        $validPackageSlugs = Package::pluck('slug')->toArray();

        $validated = $request->validate([
            'status' => 'sometimes|in:enrolled,contract_signed,completed,cancelled',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'postal_code' => 'sometimes|string|max:20',
            'city' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:students,email,' . $student->id,
            'id_document_number' => 'sometimes|string|max:50',
            'entity_type' => 'sometimes|in:individual,company',
            'payment_method' => 'sometimes|in:full,installments',
            'package_type' => ['sometimes', Rule::in($validPackageSlugs)],
            'company_name' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'company_postal_code' => 'nullable|string|max:20',
            'company_city' => 'nullable|string|max:255',
            'company_country' => 'nullable|string|max:255',
            'company_registration' => 'nullable|string|max:50',
        ]);

        $student->update($validated);

        return response()->json($student);
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json(null, 204);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => Student::count(),
            'enrolled' => Student::where('status', 'enrolled')->count(),
            'contract_signed' => Student::where('status', 'contract_signed')->count(),
            'completed' => Student::where('status', 'completed')->count(),
            'pius_plus' => Student::where('package_type', 'pius-plus')->count(),
            'pius_pro' => Student::where('package_type', 'pius-pro')->count(),
        ]);
    }
}
