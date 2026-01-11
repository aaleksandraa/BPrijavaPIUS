<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContractSignedNotification;
use App\Mail\ContractToStudent;
use App\Models\Contract;
use App\Models\Package;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class ContractController extends Controller
{
    public function index(): JsonResponse
    {
        $contracts = Contract::with('student')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($contracts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'signature_data' => 'required|string',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        // Get package by slug
        $package = Package::where('slug', $student->package_type)->first();

        if (!$package) {
            return response()->json(['error' => 'Paket nije pronađen'], 404);
        }

        // Determine which template to use based on entity type
        $template = $student->entity_type === 'company'
            ? $package->contract_template_company
            : $package->contract_template_individual;

        if (!$template) {
            return response()->json(['error' => 'Ugovor nije pronađen za ovaj paket'], 404);
        }

        // Generate contract content
        $contractContent = $this->replaceTemplatePlaceholders($template, $student, $package);

        $contract = Contract::create([
            'student_id' => $student->id,
            'contract_number' => Contract::generateContractNumber(),
            'contract_type' => $student->entity_type,
            'contract_content' => $contractContent,
            'signature_data' => $validated['signature_data'],
            'signed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Update student status
        $student->update(['status' => 'contract_signed']);

        // Send contract PDF to student FIRST
        try {
            \Log::info('Sending contract email to student: ' . $student->email);
            Mail::to($student->email)
                ->send(new ContractToStudent($student, $contract));
            \Log::info('Contract email sent successfully to: ' . $student->email);
        } catch (\Exception $e) {
            \Log::error('Failed to send contract to student: ' . $e->getMessage());
            \Log::error('Student email: ' . $student->email);
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }

        // Then send notification to admin at info@pius-academy.com
        try {
            \Log::info('Sending admin notification to: info@pius-academy.com');
            Mail::to('info@pius-academy.com')
                ->send(new ContractSignedNotification($student, $contract));
            \Log::info('Admin notification sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notification email: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }

        return response()->json($contract, 201);
    }

    public function show(Contract $contract): JsonResponse
    {
        return response()->json($contract->load('student'));
    }

    public function downloadPdf(Contract $contract): Response
    {
        $student = $contract->student;

        $pdf = Pdf::loadView('pdf.contract', [
            'contract' => $contract,
            'student' => $student,
        ]);

        $filename = sprintf(
            '%s_%s_%s_%s.pdf',
            strtoupper(str_replace('-', '_', $student->package_type)),
            $student->first_name,
            $student->last_name,
            now()->format('Y-m-d')
        );

        return $pdf->download($filename);
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        // Get package by slug
        $package = Package::where('slug', $student->package_type)->first();

        if (!$package) {
            return response()->json(['error' => 'Paket nije pronađen'], 404);
        }

        // Determine which template to use based on entity type
        $template = $student->entity_type === 'company'
            ? $package->contract_template_company
            : $package->contract_template_individual;

        if (!$template) {
            return response()->json(['error' => 'Ugovor nije pronađen za ovaj paket'], 404);
        }

        // Replace placeholders
        $content = $this->replaceTemplatePlaceholders($template, $student, $package);

        return response()->json(['content' => $content]);
    }

    private function replaceTemplatePlaceholders(string $template, Student $student, Package $package): string
    {
        $replacements = [
            '{ime}' => $student->first_name,
            '{prezime}' => $student->last_name,
            '{adresa}' => $student->address,
            '{postanskiBroj}' => $student->postal_code,
            '{mjesto}' => $student->city,
            '{grad}' => $student->city,
            '{drzava}' => $student->country,
            '{brojLicnogDokumenta}' => $student->id_document_number,
            '{telefon}' => $student->phone,
            '{email}' => $student->email,
            '{nazivFirme}' => $student->company_name ?? '',
            '{pdvBroj}' => $student->vat_number ?? '',
            '{adresaFirme}' => $student->company_address ?? '',
            '{postanskiBrojFirme}' => $student->company_postal_code ?? '',
            '{mjestoFirme}' => $student->company_city ?? '',
            '{drzavaFirme}' => $student->company_country ?? '',
            '{registracijaFirme}' => $student->company_registration ?? '',
            '{cijena}' => number_format($package->price, 2, ',', '.') . ' EUR',
            '{datum}' => now()->format('d.m.Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
