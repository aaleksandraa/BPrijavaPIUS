<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContractSignedNotification;
use App\Mail\ContractToStudent;
use App\Models\Contract;
use App\Models\ContractTemplate;
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

        // Get template
        $templateType = $student->entity_type;
        $template = ContractTemplate::where('template_type', $templateType)
            ->where('package_type', $student->package_type)
            ->first();

        if (!$template) {
            return response()->json(['error' => 'Template nije pronađen'], 404);
        }

        // Generate contract content
        $contractContent = $template->generateContent([
            'ime' => $student->first_name,
            'prezime' => $student->last_name,
            'adresa' => $student->address,
            'postanskiBroj' => $student->postal_code,
            'mjesto' => $student->city,
            'drzava' => $student->country,
            'brojLicnogDokumenta' => $student->id_document_number,
            'telefon' => $student->phone,
            'email' => $student->email,
            'nazivFirme' => $student->company_name,
            'pdvBroj' => $student->vat_number,
            'adresaFirme' => $student->company_address,
            'postanskiBrojFirme' => $student->company_postal_code,
            'mjestoFirme' => $student->company_city,
            'drzavaFirme' => $student->company_country,
            'registracijaFirme' => $student->company_registration,
        ]);

        $contract = Contract::create([
            'student_id' => $student->id,
            'contract_number' => Contract::generateContractNumber(),
            'contract_type' => $templateType,
            'contract_content' => $contractContent,
            'signature_data' => $validated['signature_data'],
            'signed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Update student status
        $student->update(['status' => 'contract_signed']);

        // Send email notification to admin
        try {
            Mail::to(config('mail.from.address'))
                ->send(new ContractSignedNotification($student, $contract));
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notification email: ' . $e->getMessage());
        }

        // Send contract PDF to student
        try {
            Mail::to($student->email)
                ->send(new ContractToStudent($student, $contract));
        } catch (\Exception $e) {
            \Log::error('Failed to send contract to student: ' . $e->getMessage());
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

        $template = ContractTemplate::where('template_type', $student->entity_type)
            ->where('package_type', $student->package_type)
            ->first();

        if (!$template) {
            return response()->json(['error' => 'Template nije pronađen'], 404);
        }

        $content = $template->generateContent([
            'ime' => $student->first_name,
            'prezime' => $student->last_name,
            'adresa' => $student->address,
            'postanskiBroj' => $student->postal_code,
            'mjesto' => $student->city,
            'drzava' => $student->country,
            'brojLicnogDokumenta' => $student->id_document_number,
            'telefon' => $student->phone,
            'email' => $student->email,
            'nazivFirme' => $student->company_name,
            'pdvBroj' => $student->vat_number,
            'adresaFirme' => $student->company_address,
            'postanskiBrojFirme' => $student->company_postal_code,
            'mjestoFirme' => $student->company_city,
            'drzavaFirme' => $student->company_country,
            'registracijaFirme' => $student->company_registration,
        ]);

        return response()->json(['content' => $content]);
    }
}
