<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageInstallment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Package::with('installments');

        // Filter by active status if requested (default: only active for public access)
        if ($request->has('include_inactive')) {
            // Admin panel can request all packages including inactive
            // No filter applied
        } else {
            // Public access - only active packages
            $query->where('is_active', true);
        }

        $packages = $query->orderBy('created_at', 'desc')->get();
        return response()->json($packages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:packages',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:installments,fixed',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'duration_days' => 'integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'show_on_landing' => 'boolean',
            'show_first_installment_reminder' => 'boolean',
            'has_contract' => 'boolean',
            'contract_template_individual' => 'nullable|string',
            'contract_template_company' => 'nullable|string',
            'installments' => 'nullable|array',
            'installments.*.installment_number' => 'required|integer|min:1',
            'installments.*.amount' => 'required|numeric|min:0',
            'installments.*.due_description' => 'nullable|string',
            'installments.*.due_date' => 'nullable|date',
            'installments.*.due_days' => 'nullable|integer',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Package::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $package = Package::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'payment_type' => $validated['payment_type'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'duration_days' => $validated['duration_days'] ?? 60,
            'features' => $validated['features'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'show_on_landing' => $validated['show_on_landing'] ?? false,
            'show_first_installment_reminder' => $validated['show_first_installment_reminder'] ?? true,
            'has_contract' => $validated['has_contract'] ?? true,
            'contract_template_individual' => $validated['contract_template_individual'] ?? null,
            'contract_template_company' => $validated['contract_template_company'] ?? null,
        ]);

        if ($validated['payment_type'] === 'installments' && !empty($validated['installments'])) {
            foreach ($validated['installments'] as $installment) {
                $package->installments()->create($installment);
            }
        }

        return response()->json($package->load('installments'), 201);
    }

    public function show(Package $package): JsonResponse
    {
        return response()->json($package->load('installments'));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $package = Package::with('installments')->where('slug', $slug)->firstOrFail();
        return response()->json($package);
    }

    public function update(Request $request, Package $package): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:packages,slug,' . $package->id,
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'payment_type' => 'sometimes|in:installments,fixed',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'duration_days' => 'sometimes|integer|min:1',
            'features' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'show_on_landing' => 'sometimes|boolean',
            'show_first_installment_reminder' => 'sometimes|boolean',
            'has_contract' => 'sometimes|boolean',
            'contract_template_individual' => 'nullable|string',
            'contract_template_company' => 'nullable|string',
            'installments' => 'nullable|array',
            'installments.*.id' => 'nullable|uuid',
            'installments.*.installment_number' => 'required|integer|min:1',
            'installments.*.amount' => 'required|numeric|min:0',
            'installments.*.due_description' => 'nullable|string',
            'installments.*.due_date' => 'nullable|date',
            'installments.*.due_days' => 'nullable|integer',
        ]);

        $package->update([
            'name' => $validated['name'] ?? $package->name,
            'slug' => $validated['slug'] ?? $package->slug,
            'price' => $validated['price'] ?? $package->price,
            'discount_price' => array_key_exists('discount_price', $validated) ? $validated['discount_price'] : $package->discount_price,
            'payment_type' => $validated['payment_type'] ?? $package->payment_type,
            'description' => $validated['description'] ?? $package->description,
            'image_url' => array_key_exists('image_url', $validated) ? $validated['image_url'] : $package->image_url,
            'duration_days' => $validated['duration_days'] ?? $package->duration_days,
            'features' => $validated['features'] ?? $package->features,
            'is_active' => $validated['is_active'] ?? $package->is_active,
            'show_on_landing' => $validated['show_on_landing'] ?? $package->show_on_landing,
            'show_first_installment_reminder' => $validated['show_first_installment_reminder'] ?? $package->show_first_installment_reminder,
            'has_contract' => $validated['has_contract'] ?? $package->has_contract,
            'contract_template_individual' => $validated['contract_template_individual'] ?? $package->contract_template_individual,
            'contract_template_company' => $validated['contract_template_company'] ?? $package->contract_template_company,
        ]);

        if (isset($validated['installments'])) {
            $package->installments()->delete();
            if ($package->payment_type === 'installments') {
                foreach ($validated['installments'] as $installment) {
                    $package->installments()->create($installment);
                }
            }
        }

        return response()->json($package->load('installments'));
    }

    public function destroy(Request $request, Package $package): JsonResponse
    {
        $forceDelete = $request->query('force', false);

        // Check if package is used by any students
        $studentCount = $package->students()->count();

        if ($studentCount > 0 && !$forceDelete) {
            return response()->json([
                'error' => 'Ne možete obrisati paket koji koriste studenti',
                'message' => "Ovaj paket koristi {$studentCount} " . ($studentCount === 1 ? 'student' : 'studenata') . ". Označite 'Prisilno brisanje' da nastavite.",
                'student_count' => $studentCount,
                'can_force_delete' => true
            ], 422);
        }

        // If force delete, update students to remove package reference
        if ($forceDelete && $studentCount > 0) {
            // Set package_type to null for all students using this package
            $package->students()->update(['package_type' => null]);
        }

        // Delete installments first
        $package->installments()->delete();

        // Delete package
        $package->delete();

        return response()->json(null, 204);
    }
}
